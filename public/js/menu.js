(function () {
    const botonMostrar = document.getElementById('menu');

    if (!botonMostrar) return;

    let menu = null; // Track dropdown

    botonMostrar.addEventListener('mouseenter', mostrarMenu);

    async function mostrarMenu(event) {
        const usernombre = event.target.dataset.nombre;
        const useremail = event.target.dataset.email;
        const siglas = getInitials(usernombre);

        // If already exists, don't recreate
        if (menu) return;

        menu = document.createElement('div');
        menu.classList.add('links');
        menu.innerHTML = `
              <div class="links-header">
                <div class="avatar">${siglas}</div>
                <div class="user-info">
                  <strong>${usernombre}</strong><br>
                  <small>${useremail}</small>
                </div>
              </div>
              <hr>
              <a href="/datospersonales">Datos Personales</a>
              <a href="/nit">NIT</a>
              <a href="/foto">Foto</a>
        `;

        document.body.appendChild(menu);

        // Animate open
        setTimeout(() => {
            menu.classList.add('links--animar');
        }, 0);

        // Optional: close if user clicks outside
        document.addEventListener('click', closeOnClickOutside);
    }

    function closeOnClickOutside(e) {
        if (menu && !menu.contains(e.target) && e.target !== botonMostrar) {
            menu.classList.add('links--cerrar');
            setTimeout(() => {
                menu.remove();
                menu = null;
            }, 300);
            document.removeEventListener('click', closeOnClickOutside);
        }
    }

    function getInitials(name) {
        if (!name) return "";
        const words = name.trim().split(/\s+/);
        return words.slice(0, 2).map(word => word.charAt(0).toUpperCase()).join("");
    }
})();
