// ======================
// Catálogo dinámico JS
// ======================
document.addEventListener("DOMContentLoaded", () => {
  // Toggle filtros en móvil
  const toggleBtn = document.querySelector(".filtros-toggle");
  const filtros = document.querySelector(".filtros");

  if (toggleBtn && filtros) {
    toggleBtn.addEventListener("click", () => {
      filtros.classList.toggle("open");
      toggleBtn.textContent = filtros.classList.contains("open")
        ? "Ocultar filtros"
        : "Mostrar filtros";
    });
  }

  // Animación extra en cards
  const cards = document.querySelectorAll(".card-prod");
  cards.forEach(card => {
    card.addEventListener("mouseenter", () => {
      card.style.transition = "transform 0.2s ease, box-shadow 0.2s ease";
      card.style.transform = "translateY(-4px) scale(1.03)";
    });
    card.addEventListener("mouseleave", () => {
      card.style.transform = "none";
    });
  });

  // Toggle inputs de precios
  const btnPrecio = document.getElementById("btn-precio");
  const precioInputs = document.querySelector(".precio-inputs");

  if (btnPrecio && precioInputs) {
    btnPrecio.addEventListener("click", () => {
      precioInputs.classList.toggle("hidden");
      btnPrecio.textContent = precioInputs.classList.contains("hidden")
        ? "¿Colocar precios?"
        : "Ocultar precios";
    });
  }
});
