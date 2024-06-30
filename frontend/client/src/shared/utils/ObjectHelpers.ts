export function filterUnassigned(obj) {
    return Object.fromEntries(Object.entries(obj).filter(([_, v]) => v !== ''));
}