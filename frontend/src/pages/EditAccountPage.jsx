import LeftSidebar from "../components/LeftSideBar";
import EditAccount from "../components/EditAccount";
import "./NewsPage.css";

export default function EditAccountPage() {
	return (
		<div>
			<main className="page">
				<LeftSidebar />
				<EditAccount />
			</main>
		</div>
	);
}