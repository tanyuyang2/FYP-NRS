import { NavLink } from "react-router-dom";
import "./AdminSidebar.css";

export default function AdminSidebar() {
	return (
		<aside className="admin-sidebar">
			<nav className="admin-nav">
				<NavLink to="/admin" end>
					Reported Content
				</NavLink>

				<NavLink to="/admin/resolved">
					Resolved Reports
				</NavLink>

				<NavLink to="/admin/moderation">
					Moderation Record
				</NavLink>

				<NavLink to="/admin/users">
					User Accounts
				</NavLink>

				<NavLink to="/admin/categories-tags">
					Categories & Tags
				</NavLink>
			</nav>
		</aside>
	);
}