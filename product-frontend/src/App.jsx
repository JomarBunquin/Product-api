import { useState } from "react";
import Login from "./components/Login";
import Products from "./components/Products";

export default function App() {
  const [loggedIn, setLoggedIn] = useState(!!localStorage.getItem("access_token"));

  return loggedIn ? (
    <Products onLogout={() => setLoggedIn(false)} />
  ) : (
    <Login onLogin={() => setLoggedIn(true)} />
  );
}