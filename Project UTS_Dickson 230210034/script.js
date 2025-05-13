document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.dropdown-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parentLi = this.closest('li');
            const menu = parentLi.querySelector('.dropdown-menu');

            if (menu) {
                menu.classList.toggle('show');
            }
        });
    });

    document.addEventListener('click', function (e) {
        toggles.forEach(toggle => {
            const parentLi = toggle.closest('li');
            const menu = parentLi.querySelector('.dropdown-menu');

            if (
                menu &&
                !toggle.contains(e.target) &&
                !menu.contains(e.target)
            ) {
                menu.classList.remove('show');
            }
        });
    });
});
