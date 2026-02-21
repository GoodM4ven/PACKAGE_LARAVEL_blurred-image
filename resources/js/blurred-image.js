import { decode, encode } from 'blurhash';

const blurComponents = {
    x: 4,
    y: 3,
};

const blurDimensions = {
    width: 32,
    height: 32,
};

const imageRequestCache = new Map();

const resolveImageSource = (src) => {
    if (imageRequestCache.has(src)) {
        return imageRequestCache.get(src);
    }

    const request = Promise.resolve(src);

    imageRequestCache.set(src, request);

    return request;
};

const loadImage = (src) =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = (...args) => reject(args);
        img.crossOrigin = 'Anonymous';
        img.src = src;
    });

const getImageData = (image) => {
    const canvas = document.createElement('canvas');
    canvas.width = image.width;
    canvas.height = image.height;
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('[blurred-image] could not get canvas context');
    }

    context.drawImage(image, 0, 0);

    return context.getImageData(0, 0, image.width, image.height);
};

const encodeImageToBlurhash = async (imageUrl) => {
    const image = await loadImage(imageUrl);
    const imageData = getImageData(image);

    return encode(
        imageData.data,
        imageData.width,
        imageData.height,
        blurComponents.x,
        blurComponents.y,
    );
};

const drawBlurredPlaceholder = (hash, canvas) => {
    const pixels = decode(hash, blurDimensions.width, blurDimensions.height);
    const context = canvas.getContext('2d');

    if (!context) {
        return;
    }

    context.putImageData(new ImageData(pixels, blurDimensions.width, blurDimensions.height), 0, 0);
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('blurredImage', (config) => ({
        thumbnailLink: config.thumbnailLink,
        link: config.link,
        element: config.element,
        fallbackLink: config.fallbackLink,
        isEagerLoaded: config.isEagerLoaded,
        isDisplayEnforced: config.isDisplayEnforced,
        imageDecoded: false,
        imgLoaded: false,
        imageFailed: false,
        visible: false,
        imageRequested: false,
        imageSrc: null,
        finalVisible: false,
        revealStarted: false,
        blurhashReady: false,
        blurhashFailed: false,
        grayHoldDone: false,
        blurhashHoldDone: false,
        blurhashHoldTimer: null,
        showGray: true,
        showBlurhash: false,
        showImage: false,
        fullIntersectFeasible: true,
        fullIntersectSafetyMargin: 96,
        resizeHandler: null,
        hashReadyEventDispatched: false,
        revealEventDispatched: false,
        init() {
            this.startGrayHold();
            this.visible = this.isDisplayEnforced;
            this.evaluateFullIntersectionFeasibility();
            this.resizeHandler = () => this.evaluateFullIntersectionFeasibility();
            window.addEventListener('resize', this.resizeHandler, { passive: true });

            this.$nextTick(() => {
                this.generateBlurImage(this.thumbnailLink, this.element);
                if (this.isDisplayEnforced || this.isEagerLoaded) {
                    this.requestImage();
                }
            });
        },
        getTestIdForEvent() {
            const root = this.element?.closest('[data-testid]');

            if (root?.dataset?.testid) {
                return root.dataset.testid;
            }

            return this.element?.dataset?.testid ?? null;
        },
        dispatchTestEvent(eventName, payload = {}) {
            if (!this.element) {
                return;
            }

            const detail = {
                testId: this.getTestIdForEvent(),
                ...payload,
            };

            if (detail.testId && typeof window !== 'undefined') {
                // Store a signal so tests can read the most recent event even if
                // they start listening after it fired.
                window.__blurredImageTestSignals = window.__blurredImageTestSignals || {};
                window.__blurredImageTestSignals[detail.testId] =
                    window.__blurredImageTestSignals[detail.testId] || {};
                window.__blurredImageTestSignals[detail.testId][eventName] = detail;
            }

            this.element.dispatchEvent(
                new CustomEvent(eventName, {
                    bubbles: true,
                    detail,
                }),
            );
        },
        dispatchHashReadyEvent() {
            if (this.hashReadyEventDispatched) {
                return;
            }

            this.hashReadyEventDispatched = true;
            this.dispatchTestEvent('blurred-image:hash-ready', {
                blurhashReady: this.blurhashReady,
                blurhashFailed: this.blurhashFailed,
            });
        },
        dispatchRevealEvent() {
            if (this.revealEventDispatched) {
                return;
            }

            this.revealEventDispatched = true;
            this.dispatchTestEvent('blurred-image:revealed', {
                imageRequested: this.imageRequested,
                imageSrc: this.imageSrc,
                imageFailed: this.imageFailed,
                showImage: this.showImage,
                finalVisible: this.finalVisible,
            });
        },
        generateBlurImage: async function (thumbnailLink, appendElement) {
            try {
                const hash = await encodeImageToBlurhash(thumbnailLink);
                const canvas = appendElement.querySelector('canvas');

                if (canvas instanceof HTMLCanvasElement) {
                    drawBlurredPlaceholder(hash, canvas);
                    this.blurhashReady = true;
                    this.startBlurhashHold();
                } else {
                    this.blurhashFailed = true;
                    this.startBlurhashHold();
                }

                this.dispatchHashReadyEvent();
                this.updateVisibility();
            } catch (error) {
                console.warn('[blurred-image] blurhash failed, falling back', error);

                this.blurhashFailed = true;
                this.dispatchHashReadyEvent();
                this.startBlurhashHold();
                this.updateVisibility();
            }
        },
        startGrayHold: function () {
            window.setTimeout(() => {
                this.grayHoldDone = true;
                this.updateVisibility();
            }, 200);
        },
        startBlurhashHold: function () {
            if (this.blurhashHoldTimer) {
                return;
            }

            this.blurhashHoldTimer = window.setTimeout(() => {
                this.blurhashHoldDone = true;
                this.updateVisibility();
            }, 600);
        },
        markVisible: function (state) {
            if (this.isDisplayEnforced) {
                this.visible = true;
                this.updateVisibility();
                this.requestImage();

                return;
            }

            if (state) {
                this.visible = true;

                this.requestImage();
                this.updateVisibility();

                return;
            }

            if (this.finalVisible) {
                return;
            }

            this.visible = false;
            this.updateVisibility();
        },
        handlePartialEnter: function () {
            this.evaluateFullIntersectionFeasibility();

            if (!this.fullIntersectFeasible) {
                this.markVisible(true);
            }
        },
        evaluateFullIntersectionFeasibility: function () {
            const elementHeight = this.element?.getBoundingClientRect().height ?? 0;
            const viewportHeight = window.innerHeight ?? 0;

            this.fullIntersectFeasible =
                elementHeight + this.fullIntersectSafetyMargin <= viewportHeight;
        },
        markLoaded: function () {
            this.imgLoaded = true;
            this.updateVisibility();
        },
        handleImageError: function (event) {
            if (this.imageFailed && event?.target?.src === this.fallbackLink) {
                this.markLoaded();

                return;
            }

            this.imageFailed = true;

            if (event?.target && event.target.src !== this.fallbackLink) {
                event.target.src = this.fallbackLink;

                return;
            }

            this.markLoaded();
        },
        requestImage: function () {
            if (this.imageRequested || !this.link) {
                return;
            }

            this.imageRequested = true;

            resolveImageSource(this.link)
                .then((source) => {
                    this.imageDecoded = true;
                    this.imageSrc = source;
                    this.updateVisibility();
                })
                .catch((error) => {
                    console.warn('[blurred-image] request failed', error);
                    imageRequestCache.delete(this.link);
                    this.imageFailed = true;
                    this.imageDecoded = true;
                    this.imageSrc = this.fallbackLink;
                    this.updateVisibility();
                });
        },
        updateVisibility: function () {
            this.showGray = !this.grayHoldDone || (!this.blurhashReady && !this.blurhashFailed);

            this.showBlurhash = this.blurhashReady && this.grayHoldDone && !this.revealStarted;

            const finalLoaded = this.imgLoaded || this.imageFailed;
            const isVisible = this.visible || this.isDisplayEnforced || this.isEagerLoaded;

            const canStartReveal =
                !this.revealStarted &&
                (this.blurhashReady || this.blurhashFailed) &&
                this.blurhashHoldDone &&
                finalLoaded &&
                (this.visible || this.isDisplayEnforced);

            if (!this.imageRequested && isVisible) {
                this.requestImage();
            }

            if (canStartReveal) {
                this.revealStarted = true;
                this.finalVisible = true;
                this.showGray = false;

                if (!this.showImage) {
                    this.showImage = true;
                }

                this.dispatchRevealEvent();
                window.requestAnimationFrame(() => {
                    this.showBlurhash = false;
                });
            }
        },
    }));
});
