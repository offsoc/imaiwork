export interface UpdateSliderIndexParams {
    type: number;
    [key: string]: any;
}

export default function useSidebar() {
    const router = useRouter();
    const route = useRoute();

    const routerParams = ref<Record<string, any>>({});

    const slider = ref<any[]>([]);
    const sliderIndex = ref<number>(0);

    const getComponents = computed(() => {
        if (sliderIndex.value == -1 || !slider.value[sliderIndex.value - 1]) {
            return null;
        }
        return slider.value[sliderIndex.value - 1].components;
    });

    const setSlider = (lists: any[]) => {
        slider.value = lists;
    };

    const getSliderIndex = (index: number) => {
        sliderIndex.value = index + 1;
        routerParams.value = {};
        pushHistory();
    };

    const updateSliderIndex = (params: UpdateSliderIndexParams) => {
        sliderIndex.value = params.type;
        routerParams.value = params;
        pushHistory();
    };

    const pushHistory = () => {
        router.replace({
            query: {
                ...routerParams.value,
                type: sliderIndex.value.toString(),
            },
        });
    };

    const init = async () => {
        await nextTick();
        const type = Number(route.query.type);
        if (type == -1 || !type || slider.value.every((item) => item.type != type)) {
            sliderIndex.value = 1;
        } else {
            sliderIndex.value = type;
        }
        routerParams.value = route.query;
        pushHistory();
    };

    init();

    return {
        slider,
        sliderIndex,
        getComponents,
        routerParams,
        init,
        setSlider,
        getSliderIndex,
        updateSliderIndex,
        pushHistory,
    };
}
