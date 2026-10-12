import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { getTimeAgo } from "../utils/date";
import api from "../api/axios";
import "./Sidebar.css";

const recommendedTopics = [
	"Esports",
	"PC Gaming",
	"Indie Games",
	"Hardware",
	"Reviews",
	"Retro Gaming",
	"Mobile"
];

export default function Sidebar() {
	const navigate = useNavigate();
	const [trendingNews, setTrendingNews] = useState([]);

	useEffect(() => {
		const loadTrendingNews = async () => {
			try {
				const response = await api.get("/article/listTrending.php");
				setTrendingNews(response.data.articles);
			} catch (error) {
				console.error("Failed to load trending news", error);
			}
		};

		loadTrendingNews();
	}, []);

	return (
		<aside className="sidebar">
			<section className="sidebar-section">
				<h2>Trending News</h2>

				<div className="trending-list">
					{trendingNews.map((article) => (
						<article 
							className="trending-item" 
							key={article.article_id}
							onClick={() => navigate(`/article/${article.article_id}`)}
						>
							<div className="trending-meta">
								In <b>{article.category}</b> by <b>{article.author}</b>
								<span>{getTimeAgo(article.published_at)}</span>
							</div>

							<h3>{article.title}</h3>
						</article>
					))}
				</div>
			</section>
			<section className="sidebar-section topics-section">
				<h2>Recommended Topics</h2>

				<div className="topic-list">
					{recommendedTopics.map((topic) => (
						<button 
							className="topic-item" 
							key={topic}
						>
							{topic}
						</button>
					))}
				</div>
			</section>
		</aside>
	);
}
