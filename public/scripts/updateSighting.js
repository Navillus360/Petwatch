window.onload = function () {
    const select = document.getElementById('userSightings');
    const formFields = document.getElementById('formFields');
    const sightingID = document.getElementById('sightingID');
    const lat = document.getElementById('latitude');
    const lng = document.getElementById('longitude');
    const comment = document.getElementById('comment');
    const preview = document.getElementById('previewImage');
    const imageInput = document.getElementById('imageInput');
    select.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        sightingID.value = option.value;
        lat.value = option.dataset.lat;
        lng.value = option.dataset.lng;
        comment.value = option.dataset.comment;
        preview.src = "/images/uploads/" + option.dataset.photo;
        formFields.style.display = 'block';
    });

    imageInput.addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
};