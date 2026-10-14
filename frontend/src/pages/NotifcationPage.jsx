import LeftSidebar from "../components/LeftSideBar";
import NotificationList from "../components/NotificationList";
import "./NewsPage.css";

export default function NotificationPage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<NotificationList />
			</main>
		</div>
	);
}