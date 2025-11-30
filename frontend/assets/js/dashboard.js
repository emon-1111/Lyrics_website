// UI only — no data, no list items

const songList = document.getElementById("song-list");

// Example: create empty placeholder cards (optional)
// remove this if you want it 100% empty

for (let i = 0; i < 6; i++) {
  const card = document.createElement("div");
  card.className = "song-card";
  songList.appendChild(card);
}
