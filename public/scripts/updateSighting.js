document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById('userSightings');
    const formFields = document.getElementById('formFields');
    const sightingID = document.getElementById('sightingID');
    const lat = document.getElementById('latitude');
    const lng = document.getElementById('longitude');
    const comment = document.getElementById('comment');
    const preview = document.getElementById('previewImage');
    const imageInput = document.getElementById('imageInput');

    function populateFields(option) {
        sightingID.value = option.value;
        lat.value = option.dataset.lat;
        lng.value = option.dataset.lng;
        comment.value = option.dataset.comment;
        preview.src = option.dataset.photo
            ? "/images/uploads/" + option.dataset.photo
            : "/images/placeholder.png";
        formFields.style.display = 'block';
    }

    select.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option) populateFields(option);
    });

    if (window.EDITING_SIGHTING_ID) {
        const option = [...select.options].find(
            opt => opt.value === String(window.EDITING_SIGHTING_ID)
        );
        if (option) {
            option.selected = true;
            populateFields(option);
        }
    }

    imageInput.addEventListener('change', function (event) {
        const [file] = event.target.files;
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
})