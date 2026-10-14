import LeftSidebar from "../components/LeftSideBar";
import UserProfile from "../components/UserProfile"
import "./NewsPage.css";

export default function UserProfilePage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<UserProfile />
			</main>
		</div>
	);
}