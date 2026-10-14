import { useState } from "react";
import { useAuth } from "../context/AuthContext";
import "./LoginModal.css";

export default function LoginModal({ onClose, onOpenRegister, onOpenForgotPassword }) {
	const [error, setError] = useState("");
	const [formData, setFormData] = useState({
		email: "",
		password: "",
	});
	const { login } = useAuth();

	const handleChange = (e) => {
		setFormData((prev) => ({
			...prev,
			[e.target.id]: e.target.value,
		}));
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		
		setError("");

		try {
			await login(formData.email, formData.password);
			onClose();
		} catch (error) {
			setError(error.response?.data?.message || "Login failed");
		}
	};

	return (
		<div className="login-modal-overlay" onClick={onClose}>
			<div className="login-modal" onClick={(e) => e.stopPropagation()}>
				<h2>Welcome Back</h2>
				<p className="login-description">Log in to continue reading, publishing and following your favorite authors</p>

				{error && <p className="error-message">{error}</p>}
				
				<form onSubmit={handleSubmit}>
					<div className="form-group">
						<label htmlFor="email">Email</label>
						<input
							id="email"
							value={formData.email}
							onChange={handleChange}
							placeholder="you@example.com"
							required
						/>
					</div>

					<div className="form-group">
						<label htmlFor="password">Password</label>
						<input
							id="password"
							type="password"
							value={formData.password}
							onChange={handleChange}
							placeholder="Enter your password"
							required
						/>
					</div>

					<button type="submit" className="login-submit">
						Log in
					</button>
				</form>
				<div className="login-modal-footer">
					<button className="forgot-password" onClick={onOpenForgotPassword}>
						Forgot your password?
					</button>

					<p>
						Don't have an account?
						<button className="signup-link" onClick={onOpenRegister}>
							Sign up
						</button>
					</p>
				</div>
			</div>
		</div>
	);
}