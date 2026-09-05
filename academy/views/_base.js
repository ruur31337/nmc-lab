// Shared auth helper — included inline in each page
// NOT a real module, just a snippet reference
const BASE_JS = `
const TOKEN = localStorage.getItem('academy_token');
const ROLE  = localStorage.getItem('academy_role');
const NAME  = localStorage.getItem('academy_name');

if (!TOKEN) { window.location = '/login'; }

function authHeaders() {
  return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
}

function logout() {
  localStorage.clear();
  window.location = '/login';
}

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
}
`;
