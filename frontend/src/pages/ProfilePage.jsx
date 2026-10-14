import LeftSidebar from "../components/LeftSideBar";
import MyProfile from "../components/MyProfile";
import "./NewsPage.css";

export default function ProfilePage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<MyProfile />
			</main>
		</div>
	);
}