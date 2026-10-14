import Sidebar from "../components/Sidebar";
import NewsList from "../components/NewsList";
import LeftSidebar from "../components/LeftSideBar";
import './NewsPage.css'

export default function LandingPage() {
	return (
		<div>
			<main className="news-page">
				<LeftSidebar />
				<NewsList />
				<Sidebar />
			</main>
		</div>
	);
}