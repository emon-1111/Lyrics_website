const wrapper = document.getElementById("parts-wrapper");

/* Helper: build a fresh part-container element */
function createPartHTML(labelVal, textVal) {
  labelVal = labelVal || "";
  textVal  = textVal  || "";
  const div = document.createElement("div");
  div.className = "part-container";
  div.innerHTML = `
    <div class="part-header">
      <input type="text" class="part-label" placeholder="Verse" value="${labelVal.replace(/"/g, '&quot;')}">
      <div class="part-actions">
        <button type="button" class="duplicate-section-btn" title="Duplicate this section">
          <i class="fa-solid fa-copy"></i>
        </button>
        <button type="button" title="Delete part">
          <i class="fa-solid fa-x"></i>
        </button>
      </div>
    </div>
    <textarea class="part-textarea" placeholder="Enter lyrics here...">${textVal}</textarea>
  `;
  return div;
}

/* GLOBAL DUPLICATE PART (top button) — duplicates the first part */
document.getElementById("duplicate-part").addEventListener("click", () => {
  const base     = wrapper.querySelector(".part-container");
  const labelVal = base.querySelector(".part-label").value;
  const textVal  = base.querySelector(".part-textarea").value;
  wrapper.appendChild(createPartHTML(labelVal, textVal));
});

/* ADD EMPTY PART */
document.getElementById("add-part").addEventListener("click", () => {
  wrapper.appendChild(createPartHTML("", ""));
});

/* DELEGATED CLICK: delete OR per-section duplicate */
wrapper.addEventListener("click", (e) => {
  const btn  = e.target.closest("button");
  if (!btn) return;
  const part = btn.closest(".part-container");
  if (!part) return;

  /* DELETE */
  if (btn.querySelector(".fa-x")) {
    if (wrapper.querySelectorAll(".part-container").length > 1) {
      part.style.opacity    = "0";
      part.style.transform  = "scale(0.96)";
      part.style.transition = "opacity 0.12s, transform 0.12s";
      setTimeout(() => part.remove(), 120);
    }
    return;
  }

  /* PER-SECTION DUPLICATE */
  if (btn.classList.contains("duplicate-section-btn")) {
    const labelVal = part.querySelector(".part-label").value;
    const textVal  = part.querySelector(".part-textarea").value;
    part.after(createPartHTML(labelVal, textVal));
    return;
  }
});

/* RESET BUTTON */
document.getElementById("reset-btn").addEventListener("click", () => {
  document
    .querySelectorAll(".create-page input:not([disabled]), .create-page textarea, .create-page select")
    .forEach(el => {
      if (el.tagName === "SELECT") el.selectedIndex = 0;
      else el.value = "";
    });

  const parts = wrapper.querySelectorAll(".part-container");
  parts.forEach((p, i) => i !== 0 && p.remove());

  const first = wrapper.querySelector(".part-container");
  first.querySelector(".part-label").value       = "";
  first.querySelector(".part-label").placeholder = "Verse";
  first.querySelector(".part-textarea").value    = "";
  first.style.opacity   = "1";
  first.style.transform = "scale(1)";
});

// INFO BOX TOGGLE
document.querySelectorAll(".info-box-toggle").forEach(icon => {
  icon.addEventListener("click", (e) => {
    e.stopPropagation();
    document.querySelectorAll(".info-box-toggle").forEach(i => {
      if (i !== icon) i.classList.remove("active");
    });
    icon.classList.toggle("active");
  });
});

document.addEventListener("click", () => {
  document.querySelectorAll(".info-box-toggle").forEach(i => i.classList.remove("active"));
});