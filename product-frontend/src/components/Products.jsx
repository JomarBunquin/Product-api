import { useEffect, useState } from "react";
import api from "../api";

const empty = { product_name: "", description: "", price: "", quantity: "" };

export default function Products({ onLogout }) {
  const [products, setProducts] = useState([]);
  const [form, setForm] = useState(empty);
  const [editingId, setEditingId] = useState(null);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = async () => {
    try {
      const { data } = await api.get("/api/products");
      setProducts(data.data);
    } catch (err) {
      setError(err.response?.data?.error || "Failed to load products");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);

  const change = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const save = async (e) => {
    e.preventDefault();
    setError("");
    try {
      if (editingId) {
        await api.put(`/api/products/${editingId}`, form);
      } else {
        await api.post("/api/products", form);
      }
      setForm(empty);
      setEditingId(null);
      load();
    } catch (err) {
      setError(err.response?.data?.error || "Save failed");
    }
  };

  const edit = (p) => {
    setEditingId(p.id);
    setForm({
      product_name: p.product_name,
      description: p.description || "",
      price: p.price,
      quantity: p.quantity,
    });
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const remove = async (id) => {
    if (!window.confirm("Delete this product?")) return;
    try {
      await api.delete(`/api/products/${id}`);
      load();
    } catch (err) {
      setError(err.response?.data?.error || "Delete failed");
    }
  };

  const logout = async () => {
    try {
      await api.post("/api/logout", { refresh_token: localStorage.getItem("refresh_token") });
    } catch (_) { /* ignore */ }
    localStorage.clear();
    onLogout();
  };

  return (
    <div className="container">
      <header>
        <h1>Product Management</h1>
        <div>
          <span className="user">Hi, {localStorage.getItem("username")}</span>
          <button className="secondary" onClick={logout}>Logout</button>
        </div>
      </header>

      <div className="card">
        <h2>{editingId ? `Edit product #${editingId}` : "Add product"}</h2>
        {error && <p className="msg error">{error}</p>}
        <form onSubmit={save}>
          <input name="product_name" placeholder="Product name" value={form.product_name} onChange={change} required />
          <textarea name="description" placeholder="Description" value={form.description} onChange={change} />
          <div className="row">
            <input name="price" type="number" step="0.01" min="0" placeholder="Price" value={form.price} onChange={change} required />
            <input name="quantity" type="number" min="0" placeholder="Quantity" value={form.quantity} onChange={change} required />
          </div>
          <div className="row">
            <button>{editingId ? "Update" : "Add"}</button>
            {editingId && (
              <button type="button" className="secondary" onClick={() => { setEditingId(null); setForm(empty); }}>
                Cancel
              </button>
            )}
          </div>
        </form>
      </div>

      <div className="card">
        <h2>Products</h2>
        {loading ? <p>Loading...</p> : products.length === 0 ? <p>No products yet.</p> : (
          <table>
            <thead>
              <tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Qty</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
              {products.map((p) => (
                <tr key={p.id}>
                  <td>{p.id}</td>
                  <td>{p.product_name}</td>
                  <td>{p.description}</td>
                  <td>₱{Number(p.price).toFixed(2)}</td>
                  <td>{p.quantity}</td>
                  <td>{p.created_at}</td>
                  <td className="actions">
                    <button onClick={() => edit(p)}>Edit</button>
                    <button className="danger" onClick={() => remove(p.id)}>Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}