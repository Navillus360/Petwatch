/**
 * Class PetWatchApp
 * This class is designed as the main point for live search and mapping
 * Allowing this as a connection point to the database and view sightings page
 */
class PetWatchApp {
    /**
     * Class constructor
     * This creates a new instance of the class
     * And pre-sets variable for use in other methods.
     */
    constructor() {
        this.map = null;
        this.markerLayer = null;
        this.searchTerm = "";
        this.statusFilter = null;
        this.sortMode = null;
        this.limit = 200;
        this.offset = 0;
        this.hasMore = true;
        this.currentCount = 0;
        this.isLoading = false;
        this.abortController = null;
        this.totalResults = 0;

    }

    /**
     * Calls functions that initialize the live search and map
     */
    init() {
        this.initMap();
        this.initSortControls();
        this.initSearch();
        this.initResetButton();
        this.initLoadMoreButton();
        this.recomputeAndRender(true);
    }

    //<editor-fold desc="Initialization">
    /**
     * Initialize the leaflet map with a preset position at Salford
     * Alongside max and min zoom as all the sightings are kept within the UK
     */
    initMap() {
        this.map = L.map('map', {
            maxZoom: 19,
            minZoom: 3
        }).setView([53.486784, -2.273801], 10);
        this.markerLayer = L.markerClusterGroup();
        this.map.addLayer(this.markerLayer);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            minZoom: 3,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(this.map);
    }

    /**
     * Initialize the sort radio buttons which help filter the sightings
     * Through the usage of event listeners
     */
    initSortControls() {
        document.querySelectorAll('input[name="sort"]').forEach(radio => {
            radio.addEventListener("change", () => {
                const value = radio.value;
                if (value === "lost" || value === "found") {
                    this.statusFilter = value;
                    this.sortMode = null;
                } else {
                    this.sortMode = value;
                    this.statusFilter = null;
                }
                this.recomputeAndRender();
            });
        });
    }

    /**
     * Initializes the search bar and ensures all user text gets converted to lowercase
     * but doesn't call the re-render function unless 2 or more characters are inputted
     */
    initSearch() {
        const input = document.getElementById("liveSearch");
        if (!input) return;
        let timer = null;
        input.addEventListener("input", () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const value = input.value.toLowerCase().trim();
                if (value.length === 0) {
                    this.searchTerm = "";
                    this.recomputeAndRender(true);
                    return;
                } else if (value.length >= 2) this.searchTerm = value;
                else return;
                this.recomputeAndRender();
            }, 250);
        });
    }

    /**
     * Initializes the reset filter button which is used to clear the search bar and radio buttons
     */
    initResetButton() {
        const btn = document.getElementById("resetFiltersBtn");
        if (!btn) return;
        btn.addEventListener("click", () => {
            this.resetFilters(true);
        });
    }

    /**
     * Initializes the show more button which loads more data onto the map and card view
     * unless all the data is loaded
     */
    initLoadMoreButton() {
        const btn = document.getElementById("showMoreBtn");
        if (!btn) return;
        btn.addEventListener("click", () => {
            if (this.hasMore) this.recomputeAndRender(false);
        });
    }

    //</editor-fold>

    //<editor-fold desc="Update Elements">
    /**
     *
     * @param sightings | objects
     * @param reset | boolean
     * Show relevant cards according to the users search.
     */
    updateCards(sightings, reset = true) {
        const container = document.getElementById("cardContainer");
        if (!container) return;
        if (reset) container.innerHTML = "";
        sightings.forEach(s => {
            const isOwner = Number(window.AUTH.userId) === Number(s.user_id);
            const isAdmin = window.AUTH.role?.toLowerCase() === "admin";
            let actionButtons = "";
            if (isOwner) {
                actionButtons += `
                <a href="/?page=update_sightings&editSighting=${s.sighting_id}" 
                   class="btn btn-sm btn-primary text-white me-2">Update</a>`;
            }
            if (isAdmin) {
                actionButtons += `
                <button class="btn btn-sm btn-danger text-white" 
                        onclick="app.deleteSighting(${s.id})">Delete</button>`;
            }
            const card = document.createElement("div");
            card.className = "col-md-4 mb-4";
            card.innerHTML = `
        <div class="card h-100 d-flex flex-row align-items-center p-2 shadow-sm">
            <img src="/images/uploads/${s.photo_url}"
                 class="me-3"
                 style="width:100px;height:100px;object-fit:cover;border-radius:10px;">
            <div class="card-body">
                <h5 class="card-title">${s.pet_name}</h5>
                <h6 class="text-muted">Reported by ${s.username}</h6>
                <p><strong>Latitude:</strong> ${s.latitude}</p>
                <p><strong>Longitude:</strong> ${s.longitude}</p>
                <p>${s.comment ?? ""}</p>
                <small class="text-muted">Date: ${s.timestamp}</small>
                <div class="mt-2">${actionButtons}</div>
            </div>
        </div>`;
            container.appendChild(card);
        });
    }

    /**
     * @param locations | objects
     * @param reset | boolean
     * Shows relevant markers on the map depending on the users search
     */
    updateMarkers(locations, reset = true) {
        if (reset) this.markerLayer.clearLayers();
        locations.forEach(location => {
            if (!location.latitude || !location.longitude) return;
            const isOwner = Number(window.AUTH.userId) === Number(location.user_id);
            const isAdmin = window.AUTH.role?.toLowerCase() === "admin";
            let actionButtons = "";
            if (isOwner) {
                actionButtons += `
                    <a href="/?page=update_sightings&editSighting=${location.sighting_id}"
                    class="btn btn-sm btn-primary text-white me-2">Update</a>`;
            }
            if (isAdmin) {
                actionButtons += `
                    <button class="btn btn-sm btn-danger text-white"
                            onclick="app.deleteSighting(${location.id})">Delete</button>`;
            }
            const marker = L.marker([
                parseFloat(location.latitude),
                parseFloat(location.longitude)
            ]);
            marker.bindPopup(`
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <img src="/images/uploads/${location.photo_url}" class="popup-img" 
                        style="width:50px;height:50px;object-fit:cover;border-radius:10px;"/>
                        <h5 class="mt-2">${location.pet_name}</h5>
                        <div class="text-muted mb-2">Reported by ${location.username}</div>
                        <div><strong>Latitude:</strong> ${location.latitude}</div>
                        <div><strong>Longitude:</strong> ${location.longitude}</div>
                        <div class="mt-2">${location.comment ?? ""}</div>
                        <small class="text-muted d-block mt-2">Date: ${location.timestamp}</small>
                        <div class="mt-2">${actionButtons}</div>
                    </div>
                </div>
            `);
            this.markerLayer.addLayer(marker);
        });
    }

    /**
     * @param loading | boolean
     * Updates the result text on the page depending on how many results were retrieved
     */
    updateResultText(loading = false) {
        const resultCount = document.getElementById("resultCount");
        const showMoreBtn = document.getElementById("showMoreBtn");
        if (!resultCount || !showMoreBtn) return;
        if (loading) {
            resultCount.textContent = "Loading results...";
            showMoreBtn.style.display = "inline-block";
            showMoreBtn.disabled = true;
            return;
        }
        if (this.totalResults === 0 && this.currentCount === 0) {
            resultCount.textContent = "No results found";
            return;
        }
        showMoreBtn.disabled = false;
        resultCount.textContent = `Showing ${this.currentCount} of ${this.totalResults} results`;
        showMoreBtn.style.display = this.hasMore ? "inline-block" : "none";
    }

    //</editor-fold>
    /**
     * @param reset | boolean
     * Whenever a sort button or live search is used,
     * it fetches the get_sightings api which retrieves data
     * from the database depending on the user input
     * And then calls functions to update the markers and cards
     */
    recomputeAndRender(reset = true) {
        //If our search is cleared
        if (reset) {
            this.offset = 0;
            this.currentCount = 0;
            this.hasMore = true;
            this.updateResultText(true);
        }
        const isSearching = this.searchTerm.length > 0;
        const params = new URLSearchParams({
            limit: isSearching ? 0 : this.limit,
            offset: isSearching ? 0 : this.offset
        });
        if (this.searchTerm) params.append("term", this.searchTerm);
        if (this.statusFilter) params.append("status", this.statusFilter);
        if (this.sortMode) params.append("sort", this.sortMode);
        if (this.abortController) this.abortController.abort();
        this.abortController = new AbortController();
        this.isLoading = true;

        //Fetch data from the database using our API
        fetch(`/api/get_sightings.php?${params}`, {
            signal: this.abortController.signal
        })
            .then(res => res.json())
            .then(data => {
                const sightings = data.sightings || [];
                this.isLoading = false;
                if (reset) {
                    this.updateMarkers(sightings, true);
                    this.updateCards(sightings, true);
                } else {
                    this.updateMarkers(sightings, false);
                    this.updateCards(sightings, false);
                }
                if (reset && this.markerLayer.getLayers().length > 0) {
                    const bounds = this.markerLayer.getBounds();
                    if (bounds.isValid()) {
                        this.map.flyToBounds(bounds, {
                            padding: [50, 50],
                            duration: 0.5
                        });
                        if (this.map.getZoom() > 16) this.map.setZoom(16);
                    }
                }

                this.currentCount += sightings.length;
                this.totalResults = data.total ?? this.currentCount;
                if (!isSearching) this.offset += sightings.length;
                if (isSearching) this.hasMore = false;
                else this.hasMore = sightings.length === this.limit;
                this.updateResultText(false);
            })
            .catch(err => {
                if (err.name === "AbortError") return;
                console.error("Fetch error:", err);
                this.isLoading = false;
                this.updateResultText(false);
            });
    }

    /**
     * @param id | int admins userID
     * Allows an admin to delete the sighting which calls the re-render function to update
     * the cards and markers which remove the deleted sighting.
     */
    deleteSighting(id) {
        if (!confirm("Are you sure you want to delete this sighting?")) return;
        fetch(`/api/delete_sighting.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": window.AUTH.csrfToken
            },
            body: JSON.stringify({id})
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) this.recomputeAndRender();
                else alert("Delete failed.");
            });
    }

    /**
     * @param clearSearch | boolean
     * @param doFetch | boolean
     * Clears the search bar and sets all the radio buttons to not selected
     */
    resetFilters(clearSearch = true, doFetch = true) {
        this.statusFilter = null;
        this.sortMode = null;
        document.querySelectorAll('input[name="sort"]').forEach(radio => {
            radio.checked = false;
        });
        if (clearSearch) {
            this.searchTerm = "";
            const searchInput = document.getElementById("liveSearch");
            if (searchInput) searchInput.value = "";
        }
        if (doFetch) this.recomputeAndRender(true);
    }
}

/**
 * Creates a new instance of the class when the DOM content is loaded
 */
let app;
document.addEventListener("DOMContentLoaded", () => {
    app = new PetWatchApp();
    app.init();
});