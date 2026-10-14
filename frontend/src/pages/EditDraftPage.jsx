import { useState } from "react";
import LeftSidebar from "../components/LeftSideBar";
import EditDraft from "../components/EditDraft";
import "./NewsPage.css";

export default function EditDraftPage() {
	const [backgroundUrl, setBackgroundUrl] = useState(null);
	return (
		<div className="article-page">
			{backgroundUrl && (
                <img
                    className="article-page-background"
                    src={backgroundUrl}
                    alt=""
                />
            )}
			<main className="page article-page-content">
				<LeftSidebar />
				<EditDraft onBackgroundChange={setBackgroundUrl} />
			</main>
		</div>
	);
}