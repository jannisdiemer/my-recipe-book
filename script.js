(() => {
    const cards = [...document.querySelectorAll('.recipe-card')];
    const search = document.getElementById('search');
    const time = document.getElementById('timeFilter');
    const difficulty = document.getElementById('difficultyFilter');
    const sort = document.getElementById('sortFilter');
    const favoritesOnly = document.getElementById('favoritesOnly');
    const count = document.getElementById('resultCount');
    const list = document.getElementById('recipeList');
    const storageKey = 'recipe-favorites-v1';
    let favorites = new Set();
    try { favorites = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]').map(String)); } catch(e) {}

    cards.forEach(card => {
        const button = card.querySelector('.favorite');
        const updateButton = () => {
            const active = favorites.has(card.dataset.id);
            button.classList.toggle('is-favorite', active);
            button.textContent = active ? '★' : '☆';
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.setAttribute('aria-label', active ? 'Favorit entfernen' : 'Als Favorit markieren');
        };
        updateButton();
        button.addEventListener('click', event => {
            event.stopPropagation();
            if (favorites.has(card.dataset.id)) favorites.delete(card.dataset.id);
            else favorites.add(card.dataset.id);
            localStorage.setItem(storageKey, JSON.stringify([...favorites]));
            updateButton();
            applyFilters();
        });
        card.addEventListener('click', () => { window.location.href = card.dataset.href; });
        button.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') event.stopPropagation(); });
    });

    function applyFilters() {
        const query = search.value.trim().toLocaleLowerCase('de');
        const selectedTime = time.value;
        const selectedDifficulty = difficulty.value;

        const checkedDiets = [
            ...document.querySelectorAll('.dietFilter:checked')
        ].map(el => el.value);

        const visible = [];

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const ingredients = card.dataset.ingredients || '';
            const description = card.dataset.description || '';

            const textMatch = !query ||
                name.includes(query) ||
                ingredients.includes(query) ||
                description.includes(query);

            const duration = Number(card.dataset.duration || 0);

            const timeMatch = !selectedTime ||
                (selectedTime === '61'
                    ? duration > 60
                    : duration > 0 && duration <= Number(selectedTime));

            const difficultyMatch = !selectedDifficulty ||
                card.dataset.difficulty === selectedDifficulty;

            const dietMatch = checkedDiets.every(
                key => card.dataset[key] === '1'
            );

            const favoriteMatch = !favoritesOnly.checked ||
                favorites.has(card.dataset.id);

            const show = textMatch &&
                timeMatch &&
                difficultyMatch &&
                dietMatch &&
                favoriteMatch;

            card.hidden = !show;

            if (show) {
                visible.push(card);
            }
        });

        visible.sort((a, b) => {
            // Bei einer aktiven Suche zuerst die relevantesten Treffer
            if (query) {
                const getRelevance = card => {
                    const name = card.dataset.name || '';
                    const ingredients = card.dataset.ingredients || '';
                    const description = card.dataset.description || '';

                    if (name === query) return 4;
                    if (name.startsWith(query)) return 3;
                    if (name.includes(query)) return 2;
                    if (ingredients.includes(query)) return 1;
                    if (description.includes(query)) return 0;

                    return -1;
                };

                const relevanceDifference =
                    getRelevance(b) - getRelevance(a);

                if (relevanceDifference !== 0) {
                    return relevanceDifference;
                }
            }

            // Anschließend die gewählte Sortierung anwenden
            if (sort.value === 'fastest') {
                return Number(a.dataset.duration || 999999) -
                    Number(b.dataset.duration || 999999);
            }

            if (sort.value === 'name') {
                return (a.dataset.name || '').localeCompare(
                    b.dataset.name || '',
                    'de'
                );
            }

            return Number(b.dataset.id) - Number(a.dataset.id);
        });

        visible.forEach(card => list.appendChild(card));

        count.textContent = `${visible.length} von ${cards.length} Rezepten`;
    }

    [search,time,difficulty,sort,favoritesOnly,...document.querySelectorAll('.dietFilter')].forEach(el => el.addEventListener('input', applyFilters));
    document.getElementById('resetFilters').addEventListener('click', () => {
        search.value = ''; time.value = ''; difficulty.value = ''; sort.value = 'newest';
        favoritesOnly.checked = false;
        document.querySelectorAll('.dietFilter').forEach(el => el.checked = false);
        applyFilters();
    });
    applyFilters();
})();