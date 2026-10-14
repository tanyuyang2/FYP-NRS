import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useNotification } from "../context/NotificationContext";
import { getTimeAgo } from "../utils/date";
import api from "../api/axios";
import Loading from "./Loading";
import "./NotificationList.css";

export default function NotificationList() {
	const navigate = useNavigate();
	const { isLoggedIn, isLoading  } = useAuth();
	const { showNotification } = useNotification();
	const [notifications, setNotifications] = useState([]);
	const [isNotificationLoading, setIsNotificationLoading] = useState(true);
	const [isMarkingRead, setIsMarkingRead] = useState(false);

	useEffect(() => {
		if (!isLoading && !isLoggedIn) {
			navigate("/");
		}
	}, [isLoading, isLoggedIn, navigate]);
	
	useEffect(() => {
		if (!isLoggedIn) {
			return;
		}

		setIsNotificationLoading(true);

		api.get("/user/listNotification.php")
			.then(response => {
				setNotifications(response.data.notifications);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load notifications",
					"error"
				);
			})
			.finally(() => {
				setIsNotificationLoading(false);
			});
	}, [isLoggedIn, showNotification]);

	const handleNotificationClick = async (notification) => {
		try {
			if (notification.is_read == 0) {
				await api.post("/user/markNotificationRead.php", {
					notification_id: notification.notification_id
				});

				setNotifications(prev =>
					prev.map(item =>
						item.notification_id === notification.notification_id
							? { ...item, is_read: 1 }
							: item
					)
				);
			}
		} catch (error) {}

		if (notification.article_id) {
			navigate(`/article/${notification.article_id}`);
		}
	};

	const handleMarkAllRead = async () => {
		if (isMarkingRead) {
			return;
		}

		setIsMarkingRead(true);

		try {
			await api.post("/user/markNotificationRead.php", {});

			setNotifications(prev =>
				prev.map(notification => ({
					...notification,
					is_read: 1
				}))
			);
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to mark notifications as read",
				"error"
			);
		} finally {
			setIsMarkingRead(false);
		}
	};

	if (isLoading || !isLoggedIn || isNotificationLoading) {
		return <Loading />;
	}

	return (
		<div className="notification-page">
			<h1>Notifications</h1>

			<div className="notification-content">
				{notifications.length === 0 ? (
					<p className="notification-empty">No notifications</p>
				) : (
					<>
						<button
							className="notification-read-all"
							onClick={handleMarkAllRead}
							disabled={isMarkingRead ||!notifications.some(notification => notification.is_read == 0)}
						>
							{isMarkingRead ? "Marking..." : "Mark all as read"}
						</button>
						{notifications.map((notification) => (
							<div
								className={`notification-item ${notification.is_read == 0 ? "unread" : ""}`}
								key={notification.notification_id}
								onClick={() => handleNotificationClick(notification)}
							>
								<p>
									<b
										onClick={(e) => {
											e.stopPropagation();
											navigate(`/profile/${notification.actor_id}`);
										}}
									>
										{notification.actor_username}
									</b> {notification.message}
								</p>
								<span>{getTimeAgo(notification.created_at)}</span>
							</div>
						))}
					</>
				)}
			</div>
		</div>
	);
}