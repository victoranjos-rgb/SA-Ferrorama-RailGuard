// Integração de rotas e mapa desenvolvida com auxílio de IA (OpenAI Codex).
const API = "../../backend/api/rotas.php",
  TRENS = "../../backend/api/trens.php",
  campos = [
    "codigo_rota",
    "nome_rota",
    "origem",
    "destino",
    "origem_lat",
    "origem_lng",
    "destino_lat",
    "destino_lng",
    "distancia_km",
    "id_trem",
    "partida_prevista",
    "chegada_prevista",
    "status_rota",
    "aviso",
  ],
  estacoes = {
    centro: {
      nome: "Joinville — Estação Centro",
      lat: -26.3044,
      lng: -48.8487,
    },
    norte: { nome: "Joinville — Estação Norte", lat: -26.2536, lng: -48.8505 },
    sul: { nome: "Joinville — Estação Sul", lat: -26.3552, lng: -48.8427 },
    araquari: { nome: "Estação Araquari", lat: -26.375, lng: -48.7183 },
    saofrancisco: {
      nome: "Estação São Francisco do Sul",
      lat: -26.2433,
      lng: -48.6386,
    },
  },
  form = document.querySelector("#form"),
  lista = document.querySelector("#lista"),
  mensagem = document.querySelector("#mensagem"),
  selectTrem = document.querySelector("#id_trem"),
  selectOrigem = document.querySelector("#estacao_origem"),
  selectDestino = document.querySelector("#estacao_destino");
if (typeof L === "undefined") {
  mensagem.textContent =
    "O mapa não carregou. Verifique a internet e atualize a página.";
  throw Error("Leaflet indisponível.");
}
const mapa = L.map("mapa").setView([-26.3044, -48.8487], 10),
  camada = L.layerGroup().addTo(mapa);
L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution: "&copy; OpenStreetMap contributors",
}).addTo(mapa);
setTimeout(() => mapa.invalidateSize(), 250);
for (const [id, e] of Object.entries(estacoes))
  for (const s of [selectOrigem, selectDestino]) {
    const o = document.createElement("option");
    o.value = id;
    o.textContent = e.nome;
    s.append(o);
  }
function distancia(a, b) {
  const rad = (n) => (n * Math.PI) / 180,
    R = 6371,
    dLat = rad(b.lat - a.lat),
    dLng = rad(b.lng - a.lng),
    x =
      Math.sin(dLat / 2) ** 2 +
      Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
}
function sincronizar() {
  const a = estacoes[selectOrigem.value],
    b = estacoes[selectDestino.value];
  if (!a || !b) return;
  const valores = {
    origem: a.nome,
    origem_lat: a.lat,
    origem_lng: a.lng,
    destino: b.nome,
    destino_lat: b.lat,
    destino_lng: b.lng,
    distancia_km: distancia(a, b).toFixed(2),
  };
  for (const [id, v] of Object.entries(valores))
    document.getElementById(id).value = v;
  camada.clearLayers();
  L.marker([a.lat, a.lng]).bindPopup(a.nome).addTo(camada);
  L.marker([b.lat, b.lng]).bindPopup(b.nome).addTo(camada);
  L.polyline(
    [
      [a.lat, a.lng],
      [b.lat, b.lng],
    ],
    { color: "#557ca8", weight: 5 },
  ).addTo(camada);
  mapa.fitBounds(
    [
      [a.lat, a.lng],
      [b.lat, b.lng],
    ],
    { padding: [35, 35] },
  );
  setTimeout(() => mapa.invalidateSize(), 0);
}
selectOrigem.addEventListener("change", sincronizar);
selectDestino.addEventListener("change", sincronizar);
async function json(r) {
  const d = await r.json();
  if (r.status === 401) {
    location.href = "../login/login.html";
    throw Error("Sessão encerrada.");
  }
  if (!r.ok) throw Error(d.erros?.join(" ") || d.erro);
  return d;
}
async function trens() {
  const d = await json(await fetch(TRENS, { credentials: "same-origin" }));
  d.trens.forEach((t) => {
    const o = document.createElement("option");
    o.value = t.id_trem;
    o.textContent = `${t.prefixo_trem} — ${t.modelo_trem}`;
    selectTrem.append(o);
  });
}
async function carregar() {
  const d = await json(await fetch(API, { credentials: "same-origin" }));
  lista.replaceChildren();
  camada.clearLayers();
  const pontos = [];
  d.rotas.forEach((r) => {
    const a = [+r.origem_lat, +r.origem_lng],
      b = [+r.destino_lat, +r.destino_lng];
    pontos.push(a, b);
    L.marker(a)
      .bindPopup(`<b>${r.origem}</b><br>${r.codigo_rota}`)
      .addTo(camada);
    L.marker(b)
      .bindPopup(`<b>${r.destino}</b><br>${r.codigo_rota}`)
      .addTo(camada);
    L.polyline([a, b], {
      color: r.status_rota === "manutencao" ? "#e1535b" : "#557ca8",
      weight: 5,
    }).addTo(camada);
    const e = document.createElement("article");
    e.className = "rota";
    e.innerHTML = `<div><strong>${r.codigo_rota} — ${r.nome_rota}</strong><p>${r.origem} → ${r.destino}</p><small>${new Date(r.partida_prevista.replace(" ", "T")).toLocaleString("pt-BR")} · <span class="status">${r.status_rota.replace("_", " ")}</span>${r.prefixo_trem ? " · " + r.prefixo_trem : ""}</small>${r.aviso ? `<p>⚠ ${r.aviso}</p>` : ""}</div><button data-id="${r.id_rota}">Excluir</button>`;
    lista.append(e);
  });
  if (pontos.length) mapa.fitBounds(pontos, { padding: [35, 35] });
  setTimeout(() => mapa.invalidateSize(), 0);
  mensagem.textContent = `${d.total} rota(s) cadastrada(s).`;
}
form.addEventListener("submit", async (e) => {
  e.preventDefault();
  sincronizar();
  const d = {};
  campos.forEach((c) => (d[c] = document.getElementById(c).value));
  try {
    await json(
      await fetch(API, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify(d),
      }),
    );
    form.reset();
    await carregar();
  } catch (x) {
    mensagem.textContent = x.message;
  }
});
lista.addEventListener("click", async (e) => {
  if (!e.target.dataset.id || !confirm("Excluir esta rota?")) return;
  try {
    await json(
      await fetch(`${API}?id=${e.target.dataset.id}`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: "{}",
      }),
    );
    await carregar();
  } catch (x) {
    mensagem.textContent = x.message;
  }
});
Promise.all([trens(), carregar()]).catch(
  (e) => (mensagem.textContent = e.message),
);
addEventListener("resize", () => mapa.invalidateSize());

