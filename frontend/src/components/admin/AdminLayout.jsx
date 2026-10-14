import { useEffect } from "react";
import { Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";
import Loading from "../Loading";
import AdminHeader from "./AdminHeader";
import AdminSidebar from "./AdminSidebar";
import "./AdminLayout.css";

export default function AdminLayout() {
	const navigate = useNavigate();
	const { user, isLoading } = useAuth();
	const isAdmin = user?.role_id == 2;

	useEffect(() => {
		if (!isLoading && !isAdmin) {
			navigate("/");
		}
	}, [isLoading, isAdmin, navigate]);

	return (
		<div className="admin-layout">
			<AdminHeader />
			<main className="admin-page">
				<AdminSidebar />
				{isLoading || !isAdmin ? <Loading /> : <Outlet />}
			</main>
		</div>
	);
}