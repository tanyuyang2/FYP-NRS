import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";
import ProfileMenu from "../ProfileMenu";
import "./AdminHeader.css";

export default function AdminHeader() {
	const navigate = useNavigate();
	const { user, logout } = useAuth();
	const [showProfileMenu, setShowProfileMenu] = useState(false);

	const handleLogout = () => {
		logout().catch(() => {});
		setShowProfileMenu(false);
	};

	return (
		<header className="admin-header">
			<div className="logo" onClick={() => navigate("/")}>P</div>
			<h1>Admin Dashboard</h1>
			<div className="header-profile">
				<button
					className="profile-avatar"
					onClick={() => setShowProfileMenu(!showProfileMenu)}
				>
					<img
						className="profile-picture"
						src={user?.profile_picture || "/default-profile.svg"}
						alt="Profile menu"
					/>
				</button>

				{showProfileMenu && (
					<ProfileMenu onLogout={handleLogout} />
				)}
			</div>
		</header>
	);
}