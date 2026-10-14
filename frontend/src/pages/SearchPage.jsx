import Sidebar from "../components/Sidebar";
import SearchList from "../components/SearchList";
import LeftSidebar from "../components/LeftSideBar";
import './NewsPage.css'

export default function SearchPage() {
	return (
		<div>
			<main className="news-page">
				<LeftSidebar />
				<SearchList />
				<Sidebar />
			</main>
		</div>
	);
}