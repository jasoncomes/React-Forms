// Format Price
export function formatPrice(num) {
    return `$${num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
}