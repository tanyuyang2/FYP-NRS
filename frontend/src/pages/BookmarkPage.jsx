import LeftSidebar from "../components/LeftSideBar";
import BookmarkList from "../components/Bookmark"
import "./NewsPage.css";

export default function BookmarkPage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<BookmarkList />
			</main>
		</div>
	);
}