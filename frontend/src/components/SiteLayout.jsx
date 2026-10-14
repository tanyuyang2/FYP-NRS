import { Outlet } from "react-router-dom";
import Header from "./Header";

export default function SiteLayout() {
	return (
		<div className="site-layout">
			<Header />
			<Outlet />
		</div>
	);
}