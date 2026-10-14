import { useEffect, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import api from "../../api/axios";
import Loading from "../Loading";
import Pagination from "../Pagination";
import ManageUser from "./ManageUser";
import "./UserAccount.css";

const statusLabels = {
	active: "Active",
	suspended: "Suspended",
	banned: "Banned"
};

const roleLabels = {
	1: "User",
	2: "Admin"
};

export default function UserAccount() {
	const [users, setUsers] = useState([]);
	const [search, setSearch] = useState("");
	const [searchTerm, setSearchTerm] = useState("");
	const [status, setStatus] = useState("all");
	const [page, setPage] = useState(1);
	const [pagination, setPagination] = useState(null);
	const [isUsersLoading, setIsUsersLoading] = useState(true);
	const { showNotification } = useNotification();
	const [selectedUserId, setSelectedUserId] = useState(null);

	useEffect(() => {
		if (selectedUserId !== null) {
			return;
		}
		setIsUsersLoading(true);
		setUsers([]);
		setPagination(null);

		api.get("/admin/listUsers.php", {
			params: {
				page,
				search: searchTerm,
				status
			}
		})
			.then(response => {
				setUsers(response.data.users);
				setPagination(response.data.pagination);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load users",
					"error"
				);
			})
			.finally(() => {
				setIsUsersLoading(false);
			});
	}, [page, searchTerm, status, showNotification, selectedUserId]);

	const handleSearch = e => {
		e.preventDefault();
		setSearchTerm(search.trim());
		setPage(1);
	};

	if (isUsersLoading) {
		return <Loading />;
	}

	if (selectedUserId !== null) {
		return (
			<ManageUser
				userId={selectedUserId}
				onBack={() => setSelectedUserId(null)}
			/>
		);
	}

	return (
		<section className="user-accounts">
			<h1>User Accounts</h1>

			<p className="users-subtitle">
				{pagination?.total || 0} {pagination?.total == 1 ? "user" : "users"}
			</p>

			<div className="users-toolbar">
				<form className="users-search" onSubmit={handleSearch}>
					<input
						type="text"
						placeholder="Search by username or email"
						value={search}
						onChange={e => setSearch(e.target.value)}
					/>

					<button type="submit">
						Search
					</button>
				</form>

				<select
					className="users-filter"
					value={status}
					onChange={e => {
						setStatus(e.target.value);
						setPage(1);
					}}
				>
					<option value="all">All Statuses</option>
					<option value="active">Active</option>
					<option value="suspended">Suspended</option>
					<option value="banned">Banned</option>
				</select>
			</div>

			<div className="users-list">
				{users.length === 0 ? (
					<p className="users-empty">No users found</p>
				) : (
					users.map(user => (
						<div className="users-item" key={user.user_id}>
							<div className="users-details">
								<p className="users-name">{user.username}</p>
								<p className="users-email">{user.email}</p>
							</div>

							<span className="users-role">
								{roleLabels[user.role_id]}
							</span>

							<span className={`users-status ${user.user_status}`}>
								{statusLabels[user.user_status]}
							</span>

							<button
								className="users-manage"
								onClick={() => setSelectedUserId(user.user_id)}
							>
								Manage
							</button>
						</div>
					))
				)}
			</div>

			{pagination && pagination.total_pages > 1 && (
				<Pagination
					page={pagination.page}
					totalPages={pagination.total_pages}
					onPageChange={setPage}
				/>
			)}
		</section>
	);
}