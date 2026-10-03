import { useState } from "react";
import api from "../api";

export default function Login({ onLogin }) {
  const [mode, setMode] = useState("login");
  const [form, setForm] = useState({ username: "", email: "", password: "" });
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");
  const [loading, setLoading] = useState(false);

  const change = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const submit = async (e) => {
    e.preventDefault();
    setError("");
    setInfo("");
    setLoading(true);
    try {
      if (mode === "register") {
        await api.post("/api/register", form);
        setInfo("Account created. You can now log in.");
        setMode("login");
      } else {
        const { data } = await api.post("/api/login", {
          login: form.username,
          password: form.password,
        });
        localStorage.setItem("access_token", data.tokens.access_token);
        localStorage.setItem("refresh_token", data.tokens.refresh_token);
        localStorage.setItem("username", data.user.username);
        onLogin();
      }
    } catch (err) {
      setError(err.response?.data?.error || "Cannot reach the server");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card auth">
      <h2>{mode === "login" ? "Login" : "Create account"}</h2>
      {error && <p className="msg error">{error}</p>}
      {info && <p className="msg ok">{info}</p>}
      <form onSubmit={submit}>
        <input name="username" placeholder={mode === "login" ? "Username or email" : "Username"}
               value={form.username} onChange={change} required />
        {mode === "register" && (
          <input name="email" type="email" placeholder="Email"
                 value={form.email} onChange={change} required />
        )}
        <input name="password" type="password" placeholder="Password"
               value={form.password} onChange={change} required />
        <button disabled={loading}>
          {loading ? "Please wait..." : mode === "login" ? "Login" : "Register"}
        </button>
      </form>
      <p className="switch">
        {mode === "login" ? "No account? " : "Have an account? "}
        <a href="#" onClick={(e) => { e.preventDefault(); setMode(mode === "login" ? "register" : "login"); }}>
          {mode === "login" ? "Register" : "Login"}
        </a>
      </p>
    </div>
  );
}