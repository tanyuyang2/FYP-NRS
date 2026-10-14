import LeftSidebar from "../components/LeftSideBar";
import Tasks from "../components/Tasks";
import "./NewsPage.css";

export default function TasksPage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<Tasks />
			</main>
		</div>
	);
}