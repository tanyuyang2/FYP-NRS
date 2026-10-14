import { useEffect, useState } from "react";
import { useAuth } from "../../context/AuthContext";
import { useNotification } from "../../context/NotificationContext";
import { getTimeAgo } from "../../utils/date";
import { ArrowLeft } from "lucide-react";
import api from "../../api/axios";
import IssueWarningModal from "./IssueWarningModal";
import SuspendUserModal from "./SuspendUserModal";
import Loading from "../Loading";
import "./ManageUser.css";

const statusLabels = {
	active: "Active",
	suspended: "Suspended",
	banned: "Banned"
};

const actionLabels = {
	warning: "Warning issued",
	suspension: "Account suspended",
	ban: "Account banned",
	lift_suspension: "Suspension lifted",
	unban: "Account unbanned"
};

const roleLabels = {
	1: "User",
	2: "Admin"
};

export default function ManageUser({ userId, onBack }) {
	const { showNotification } = useNotification();
	const [user, setUser] = useState(null);
	const [history, setHistory] = useState([]);
	const [isUserLoading, setIsUserLoading] = useState(true);
	const { user: currentUser } = useAuth();
	const isOwnAccount = currentUser?.user_id == userId;
	const [selectedRole, setSelectedRole] = useState("");
	const [showWarningModal, setShowWarningModal] = useState(false);
	const [showSuspendModal, setShowSuspendModal] = useState(false);
	const [isLoading, setIsLoading] = useState(false);
	const [refreshCount, setRefreshCount] = useState(0);

	useEffect(() => {
		setIsUserLoading(true);
		setUser(null);
		setHistory([]);
	}, [userId]);

	useEffect(() => {
		api.get(`/admin/getUserDetails.php?id=${userId}`)
			.then(response => {
				setUser(response.data.user);
				setHistory(response.data.history);
				setSelectedRole(String(response.data.user.role_id));
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load user",
					"error"
				);
			})
			.finally(() => {
				setIsUserLoading(false);
			});
	}, [userId, refreshCount, showNotification]);

	const handleRoleChange = async e => {
		e.preventDefault();
		if (isLoading || isOwnAccount) {
			return;
		}
		setIsLoading(true);
		try {
			const response = await api.post("/admin/changeUserRole.php", {
				user_id: userId,
				role_id: selectedRole
			});

			setUser(prev => ({
				...prev,
				role_id: response.data.role_id
			}));

			showNotification(response.data.message, "success");
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to change user role",
				"error"
			);
		} finally {
			setIsLoading(false);
		}
	};

	const handleAccountAction = async (action, reason = "", duration = "") => {
		if (isLoading || isOwnAccount) {
			return;
		}

		setIsLoading(true);

		try {
			const response = await api.post("/admin/manageUserAction.php", {
				user_id: userId,
				action,
				reason,
				duration
			});

			showNotification(response.data.message, "success");
			setShowWarningModal(false);
			setShowSuspendModal(false);
			setRefreshCount(prev => prev + 1);
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to update user",
				"error"
			);
			return false;
		} finally {
			setIsLoading(false);
		}
	};

	if (isUserLoading) {
		return <Loading />;
	}

	if (!user) {
		return (
			<section className="manage-user">
				<button className="review-back" onClick={onBack}>
					<ArrowLeft size={18} />
					Back to User Accounts
				</button>
			</section>
		);
	}

	return (
		<section className="manage-user">
			<button className="review-back" onClick={onBack}>
				<ArrowLeft size={18} />
				Back to User Accounts
			</button>

			<h1>Manage User</h1>

			<div className="manage-content">
				<div className="manage-details">
					<div className="manage-profile">
						<img
							className="manage-avatar"
							src={user.profile_picture || "/default-profile.svg"}
							alt={user.username}
						/>

						<div className="manage-info">
							<h2>{user.username}</h2>

							<div className="manage-badges">
								<span className="manage-role">
									{roleLabels[user.role_id]}
								</span>

								<span className={`manage-status ${user.user_status}`}>
									{statusLabels[user.user_status]}
								</span>
							</div>
						</div>
					</div>

					<h3>User ID</h3>
					<p className="manage-value">#{user.user_id}</p>

					<h3>Email</h3>
					<p className="manage-value">{user.email}</p>

					{user.user_status === "suspended" && (
						<>
							<h3>Suspended until</h3>
							<p className="manage-value">
								{new Date(
									user.suspended_until.replace(" ", "T") + "+08:00"
								).toLocaleString("en-GB")}
							</p>
						</>
					)}

					<a
						className="manage-profile-link"
						href={`/profile/${user.user_id}`}
						target="_blank"
					>
						View public profile
					</a>
				</div>

				<div className="manage-history">
					<div className="manage-history-inner">
						<h2>Moderation History</h2>
						<div className="manage-history-content">
							{history.length === 0 ? (
								<p>No moderation history</p>
							) : (
								history.map(record => (
									<div className="manage-history-item" key={record.history_id}>
										<p className="manage-reason">
											{actionLabels[record.action]}
											{record.reason && ` — ${record.reason}`}
										</p>

										<p className="manage-history-meta">
											{getTimeAgo(record.created_at)} · by {record.actor}
										</p>
									</div>
								))
							)}
						</div>
					</div>
				</div>
			</div>

			<div className="manage-stats">
				<div className="manage-stat">
					<h2>{user.article_count}</h2>
					<p>Articles Published</p>
				</div>

				<div className="manage-stat">
					<h2>{user.comment_count}</h2>
					<p>Comments Posted</p>
				</div>

				<div className="manage-stat">
					<h2>{user.pending_report_count}</h2>
					<p>Pending Reports</p>
				</div>

				<div className="manage-stat">
					<h2>{user.warning_count}</h2>
					<p>Warnings Issued</p>
				</div>
			</div>

			<div className="manage-actions">
				<div className="manage-account-actions">
					<h2>Account Actions</h2>

					<form onSubmit={handleRoleChange}>
						<label htmlFor="manage-role">Role</label>

						<div className="manage-role-controls">
							<select
								id="manage-role"
								value={selectedRole}
								onChange={e => setSelectedRole(e.target.value)}
								disabled={isLoading || isOwnAccount}
							>
								<option value="1">User</option>
								<option value="2">Admin</option>
							</select>

							<button
								type="submit"
								disabled={isLoading || isOwnAccount || selectedRole == user.role_id}
							>
								Save
							</button>
						</div>
					</form>
				</div>

				<div className="manage-danger-zone">
					<h2>Danger Zone</h2>

					<p className="manage-danger-description">
						These actions affect the user's account access and moderation history.
					</p>

					<div className="manage-danger-buttons">
						<button
							className="manage-warning"
							onClick={() => setShowWarningModal(true)}
							disabled={isLoading || isOwnAccount}
						>
							Issue Warning
						</button>

						<button
							className="manage-restore"
							onClick={() => handleAccountAction("lift_suspension")}
							disabled={isLoading || isOwnAccount || user.user_status !== "suspended"}
						>
							Lift Suspension
						</button>

						<button
							className="manage-suspend"
							onClick={() => setShowSuspendModal(true)}
							disabled={isLoading || isOwnAccount || user.user_status === "banned"}
						>
							Suspend/Ban User
						</button>

						<button
							className="manage-restore"
							onClick={() => handleAccountAction("unban")}
							disabled={isLoading || isOwnAccount || user.user_status !== "banned"}
						>
							Unban User
						</button>
					</div>
				</div>
			</div>

			{showWarningModal && (
				<IssueWarningModal
					username={user.username}
					warningCount={user.warning_count}
					suspensionCount={user.suspension_count}
					fromReport={false}
					onConfirm={reason => handleAccountAction("warning", reason)}
					onClose={() => setShowWarningModal(false)}
				/>
			)}

			{showSuspendModal && (
				<SuspendUserModal
					username={user.username}
					warningCount={user.warning_count}
					suspensionCount={user.suspension_count}
					fromReport={false}
					onConfirm={(duration, reason) =>
						handleAccountAction(
							duration === "ban" ? "ban" : "suspension",
							reason,
							duration
						)
					}
					onClose={() => setShowSuspendModal(false)}
				/>
			)}
		</section>
	);
}