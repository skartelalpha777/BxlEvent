(function () {
    const list = document.getElementById('ticket-types-list');
    const addButton = document.getElementById('add-ticket-type');

    addButton.addEventListener('click', function () {
        const index = list.dataset.index;
        const prototype = list.dataset.prototype.replace(/__name__/g, index);
        const wrapper = document.createElement('div');
        wrapper.classList.add('ticket-type-row', 'border', 'border-secondary', 'border-opacity-25', 'rounded-3', 'p-3', 'mb-3');
        wrapper.innerHTML = prototype + '<button type="button" class="btn btn-outline-danger btn-sm remove-ticket-type mt-2">Retirer ce type de billet</button>';
        list.appendChild(wrapper);
        list.dataset.index = parseInt(index, 10) + 1;
    });

    list.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-ticket-type')) {
            e.target.closest('.ticket-type-row').remove();
        }
    });
})();

// Suppression d'une affiche existante (page d'édition uniquement) : coche la case cachée
// correspondante puis cache la miniature, sans recharger la page.
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-gallery-link')) {
        e.preventDefault();
        const galleryId = e.target.dataset.galleryId;
        document.getElementById('delete-gallery-' + galleryId).checked = true;
        document.getElementById('gallery-' + galleryId).style.display = 'none';
    }
});