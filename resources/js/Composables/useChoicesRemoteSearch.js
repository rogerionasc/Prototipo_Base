import { ref, nextTick, onMounted, onUnmounted } from "vue";

export function useChoicesRemoteSearch({
    selectRef,
    getSelectedValue,
    getRows,
    fetchRows,
    makeLabel,
    refreshChoices,
    attachSearch = true,
    debounceMs = 250,
    attachRetryMs = 250,
    attachRetries = 20,
    suppressMsAfterClear = 300,
    attachDelayAfterSync = 25,
    placeholderLabel = "Selecione",
    placeholderDisabled = true,
}) {
    const loading = ref(false);

    let lastQuery = "";
    let suppressUntil = 0;
    let syncing = false;
    let reqSeq = 0;
    let timer = null;
    let cleanup = null;
    let attachTimer = null;

    function getWrapper(sel) {
        try {
            if (!sel) return null;
            const closest = sel.closest ? sel.closest(".choices") : null;
            if (closest) return closest;
            const parent = sel.parentElement;
            if (!parent) return null;
            const kids = Array.from(parent.children || []);
            const idx = kids.indexOf(sel);
            if (idx >= 0) {
                for (let i = idx + 1; i < kids.length; i += 1) {
                    if (kids[i]?.classList?.contains("choices")) return kids[i];
                }
                for (let i = idx - 1; i >= 0; i -= 1) {
                    if (kids[i]?.classList?.contains("choices")) return kids[i];
                }
            }
            return parent.querySelector(".choices");
        } catch (e) {
            return null;
        }
    }

    function getSearchInput(sel) {
        try {
            const wrapper = getWrapper(sel);
            const input = wrapper?.querySelector("input.choices__input--cloned") || wrapper?.querySelector("input.choices__input");
            return input || null;
        } catch (e) {
            return null;
        }
    }

    async function syncChoices() {
        await nextTick();
        const sel = selectRef?.value ?? null;
        if (!sel) return;

        let inst = sel._choicesInstance || sel.choices;
        const wrapper = getWrapper(sel);
        const wasOpen = !!wrapper?.classList?.contains?.("is-open");
        const selectedValue = String(getSelectedValue?.() ?? "");
        const rows = Array.isArray(getRows?.()) ? getRows() : [];

        const items = [
            { value: "", label: placeholderLabel, selected: selectedValue === "", disabled: !!placeholderDisabled },
            ...rows.map((r) => ({
                value: String(r?.id ?? r?.value ?? ""),
                label: String(makeLabel ? makeLabel(r) : (r?.label ?? "")),
                selected: String(r?.id ?? r?.value ?? "") === selectedValue,
            })),
        ];

        syncing = true;
        try {
            if ((!inst || typeof inst.setChoices !== "function") && typeof refreshChoices === "function") {
                try { refreshChoices(sel); } catch (e) { }
                inst = sel._choicesInstance || sel.choices;
            }
            if (inst && typeof inst.setChoices === "function") {
                try { if (typeof inst.removeActiveItems === "function") inst.removeActiveItems(); } catch (e) { }
                try { if (typeof inst.clearChoices === "function") inst.clearChoices(); } catch (e) { }
                inst.setChoices(items, "value", "label", true);
            }
            try { sel.value = selectedValue; } catch (e) { }
            if (inst && typeof inst.setChoiceByValue === "function") {
                if (selectedValue) {
                    try { inst.setChoiceByValue(selectedValue); } catch (e) { }
                } else {
                    try { if (typeof inst.removeActiveItems === "function") inst.removeActiveItems(); } catch (e) { }
                    try { inst.setChoiceByValue(""); } catch (e) { }
                }
            }
            if (wasOpen && inst && typeof inst.showDropdown === "function") {
                try { inst.showDropdown(); } catch (e) { }
            }
        } finally {
            setTimeout(() => { syncing = false; }, 0);
            setTimeout(() => { attach(); }, attachDelayAfterSync);
        }
    }

    function clearSearch() {
        const sel = selectRef?.value ?? null;
        if (!sel) return;
        try {
            const input = getSearchInput(sel);
            if (!input) return;
            suppressUntil = Date.now() + Number(suppressMsAfterClear || 0);
            input.value = "";
            lastQuery = "";
        } catch (e) { }
    }

    async function runSearch(q) {
        const query = String(q || "").trim();
        const myReq = ++reqSeq;
        if (!query) {
            try { await fetchRows?.(""); } catch (e) { }
            if (myReq !== reqSeq) return;
            await syncChoices();
            return;
        }
        loading.value = true;
        try {
            await fetchRows(query);
        } finally {
            if (myReq !== reqSeq) return;
            loading.value = false;
            await syncChoices();
        }
    }

    function attach() {
        const sel = selectRef?.value ?? null;
        const input = sel ? getSearchInput(sel) : null;
        if (!input) return false;

        if (typeof cleanup === "function") {
            try { cleanup(); } catch (e) { }
            cleanup = null;
        }

        const onInput = (e) => {
            if (Date.now() < suppressUntil) return;
            if (syncing) return;
            const q = String(e?.target?.value ?? "").trim();
            if (q === lastQuery) return;
            lastQuery = q;
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => { runSearch(q); }, debounceMs);
        };

        input.addEventListener("input", onInput);
        cleanup = () => {
            try { input.removeEventListener("input", onInput); } catch (e) { }
        };
        return true;
    }

    function startAutoAttach() {
        let tries = 0;
        attachTimer = setInterval(() => {
            tries += 1;
            const ok = attach();
            if (ok || tries >= attachRetries) {
                try { clearInterval(attachTimer); } catch (e) { }
                attachTimer = null;
            }
        }, attachRetryMs);
    }

    function destroy() {
        if (timer) { try { clearTimeout(timer); } catch (e) { } timer = null; }
        if (attachTimer) { try { clearInterval(attachTimer); } catch (e) { } attachTimer = null; }
        if (typeof cleanup === "function") { try { cleanup(); } catch (e) { } cleanup = null; }
    }

    onMounted(() => { if (attachSearch) startAutoAttach(); });
    onUnmounted(() => { destroy(); });

    return {
        loading,
        syncChoices,
        clearSearch,
        attach,
        destroy,
        runSearch,
    };
}
