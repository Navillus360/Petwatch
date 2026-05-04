/**
 * When the content is loaded, show the map first
 * Otherwise, the map won't show if we show only the cards first
 */
window.onload = function () {
    toggleMap(true);
};

/**
 * @param showMap | boolean
 * Allows the user to show the map or bootstrap cards
 *  If the map view is selected, hides the cards and shows the map
 *  If the card view is selected, hides the map and shows the cards
 */
function toggleMap(showMap) {
    const mapSection = document.getElementById('mapping');
    const cardSection = document.getElementById('views');
    const mapBtn = document.getElementById('showMapBtn');
    const cardBtn = document.getElementById('showCardBtn');
    if (showMap) {
        mapSection.style.display = 'block';
        cardSection.style.display = 'none';
        mapBtn.classList.add('btn-dark');
        mapBtn.classList.remove('btn-secondary');
        cardBtn.classList.add('btn-secondary');
        cardBtn.classList.remove('btn-dark');
        if (window.app && app.map) {
            setTimeout(() => {
                app.map.invalidateSize();
            }, 50);
        }
    } else {
        mapSection.style.display = 'none';
        cardSection.style.display = 'block';
        cardBtn.classList.add('btn-dark');
        cardBtn.classList.remove('btn-secondary');
        mapBtn.classList.add('btn-secondary');
        mapBtn.classList.remove('btn-dark');
    }
}