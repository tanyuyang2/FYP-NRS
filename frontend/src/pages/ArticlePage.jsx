import { useState } from "react";
import Sidebar from "../components/Sidebar";
import LeftSidebar from "../components/LeftSideBar";
import Article from "../components/Article";
import './NewsPage.css'

export default function ArticlePage() {
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
            <main className="news-page article-page-content">
                <LeftSidebar />
                <Article onBackgroundChange={setBackgroundUrl} />
                <Sidebar />
            </main>
        </div>
    );
}