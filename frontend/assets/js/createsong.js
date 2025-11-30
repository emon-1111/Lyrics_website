const createSongSection = document.getElementById("create-song");
const partsContainer = document.getElementById("parts-container");
const duplicateBtn = document.getElementById("duplicate-part");
const addEmptyBtn = document.getElementById("add-empty-part");

// Show Create Song Section when clicking sidebar button
document.querySelector('.menu-btn:nth-child(6)').addEventListener('click', () => {
  createSongSection.style.display = 'block';
  document.getElementById("song-list").style.display = "none"; // hide song list
});

// Function to add actions to each part
function addPartActions(part) {
  const moveUpBtn = part.querySelector(".move-up");
  const moveDownBtn = part.querySelector(".move-down");
  const removeBtn = part.querySelector(".remove-part");

  moveUpBtn.addEventListener("click", () => {
    const prev = part.previousElementSibling;
    if(prev) partsContainer.insertBefore(part, prev);
  });

  moveDownBtn.addEventListener("click", () => {
    const next = part.nextElementSibling;
    if(next) partsContainer.insertBefore(next, part);
  });

  removeBtn.addEventListener("click", () => {
    partsContainer.removeChild(part);
  });
}

// Duplicate Part
duplicateBtn.addEventListener("click", () => {
  const firstPart = partsContainer.querySelector(".song-part");
  if(firstPart) {
    const clone = firstPart.cloneNode(true);
    partsContainer.appendChild(clone);
    addPartActions(clone);
  }
});

// Add Empty Part
addEmptyBtn.addEventListener("click", () => {
  const newPart = document.createElement("div");
  newPart.className = "song-part";
  newPart.innerHTML = `
    <div class="part-header">
      <input type="text" class="part-name" placeholder="Verse">
      <div class="part-actions">
        <button type="button" class="move-up">↑</button>
        <button type="button" class="move-down">↓</button>
        <button type="button" class="remove-part">✕</button>
      </div>
    </div>
    <textarea class="part-lyrics" placeholder="Write lyrics here..."></textarea>
  `;
  partsContainer.appendChild(newPart);
  addPartActions(newPart);
});

// Initialize actions for first part
document.querySelectorAll(".song-part").forEach(addPartActions);
