import axios from "axios";

const api = axios.create({ baseURL: import.meta.env.VITE_API_URL });

// Attach the token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("access_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Expired/invalid token -> back to login
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const isLogin = err.config?.url?.includes("/api/login");
    if (err.response?.status === 401 && !isLogin) {
      localStorage.clear();
      window.location.reload();
    }
    return Promise.reject(err);
  }
);

export default api;