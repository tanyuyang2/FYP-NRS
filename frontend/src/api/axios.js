import axios from "axios";

// Set per environment in .env.development / .env.production.
// In production this is a same-origin relative path, so moving from
// http://46.250.233.81 to https://example.com needs no change here.
const api = axios.create({
	baseURL: import.meta.env.VITE_API_URL,
	withCredentials: true
});

export default api;
