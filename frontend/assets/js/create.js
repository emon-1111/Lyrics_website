const wrapper = document.getElementById("parts-wrapper");

/* DUPLICATE PART */
document.getElementById("duplicate-part").addEventListener("click", () => {
  const base = wrapper.querySelector(".part-container");
  const clone = base.cloneNode(true);
  wrapper.appendChild(clone);
});

/* ADD EMPTY PART */
document.getElementById("add-part").addEventListener("click", () => {
  const base = wrapper.querySelector(".part-container").cloneNode(true);
  base.querySelector("input").value = "";
  base.querySelector("textarea").value = "";
  wrapper.appendChild(base);
});

/* DELETE PART */
wrapper.addEventListener("click", (e) => {
  const btn = e.target.closest("button");
  const part = btn?.closest(".part-container");
  if (!part) return;

  if (btn.querySelector(".fa-x")) {
    if (wrapper.querySelectorAll(".part-container").length > 1) {
      part.style.opacity = "0";
      part.style.transform = "scale(0.96)";
      setTimeout(() => part.remove(), 120);
    }
  }
});

/* RESET BUTTON */
document.getElementById("reset-btn").addEventListener("click", () => {
  document
    .querySelectorAll(".create-page input:not([disabled]), .create-page textarea")
    .forEach(el => el.value = "");

  const parts = wrapper.querySelectorAll(".part-container");
  parts.forEach((p, i) => i !== 0 && p.remove());

  const first = wrapper.querySelector(".part-container");
  first.querySelector("input").placeholder = "Verse";
  first.querySelector("input").value = "";
  first.querySelector("textarea").value = "";
  first.style.opacity = "1";
  first.style.transform = "scale(1)";
});

// INFO BOX TOGGLE
document.querySelectorAll(".info-box-toggle").forEach(icon => {
  icon.addEventListener("click", (e) => {
    e.stopPropagation(); // prevent closing immediately
    // close other boxes
    document.querySelectorAll(".info-box-toggle").forEach(i => {
      if(i !== icon) i.classList.remove("active");
    });
    // toggle current box
    icon.classList.toggle("active");
  });
});

// close info box if clicking outside
document.addEventListener("click", () => {
  document.querySelectorAll(".info-box-toggle").forEach(i => i.classList.remove("active"));
});
