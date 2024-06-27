import { ref, onMounted, onUnmounted } from 'vue';
import type { PreparedPhotoSwipeOptions, SlideData } from 'photoswipe';
import type { Ref } from 'vue';



import PhotoSwipe from "photoswipe";

export function usePhotoSwipe() {
    const pswpElement: Ref<HTMLDivElement | null> = ref(null);
    let gallery: PhotoSwipe | null = null;

    const openGallery = (items: SlideData[], index: number) => {
        if (!pswpElement.value) return;
console.log('index', index)
        const options: PreparedPhotoSwipeOptions = {
            dataSource: items,
            bgOpacity: 0.7,
            spacing: 0.12,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            maxWidthToAnimate: 0.5,
            allowPanToNext: true,
            loop: true,
            pinchToClose: true,
            closeOnVerticalDrag: true,
            hideAnimationDuration: 333,
            showAnimationDuration: 333,
            zoomAnimationDuration: 333,
            escKey: true,
            arrowKeys: true,
            trapFocus: true,
            returnFocus: true,
            clickToCloseNonZoomable: true,
            imageClickAction: false,
            bgClickAction: false,
            wheelToZoom: true,
            tapAction: false,
            doubleTapAction: false,
            preloaderDelay: 200,
            indexIndicatorSep: ' of ',
            errorMsg: 'This image could not be loaded',
            preload: [1, 1],
            index: index,
            gallery: pswpElement.value  // Using gallery property
        };

        // Initialize the gallery
        gallery = new PhotoSwipe(options);
        gallery.init();
    };

    onUnmounted(() => {
        gallery?.destroy();
        gallery = null;
    });

    return { pswpElement, openGallery };
}