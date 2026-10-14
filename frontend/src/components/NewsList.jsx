import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useNotification } from "../context/NotificationContext";
import { ListSortDescending } from "lucide-react";
import api from "../api/axios";
import NewsCard from "./NewsCard";
import Pagination from "./Pagination";
import Loading from "./Loading";
import "./NewsList.css"

// The sort button cycles through these in order.
const sortOptions = [
	{ value: "latest", label: "Latest" },
	{ value: "trending", label: "Trending" },
	{ value: "helpful", label: "Most helpful" }
];

export default function NewsList() {
	const [articles, setArticles] = useState([]);
	const navigate = useNavigate();
	const { isLoggedIn, isLoading } = useAuth();
	const [page, setPage] = useState(1);
	const [pagination, setPagination] = useState(null);
	const { showNotification } = useNotification();
	const [activeTab, setActiveTab] = useState(null);
	const [isArticleLoading, setIsArticleLoading] = useState(true);
	const [categories, setCategories] = useState([]);
	const visibleCategories = categories.slice(0, 4);
	const moreCategories = categories.slice(4);
	const [showMoreCategories, setShowMoreCategories] = useState(false);
	const [sortBy, setSortBy] = useState("latest");

	useEffect(() => {
		api.get("/article/listCategory.php")
			.then((response) => {
				setCategories(response.data.categories);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load categories",
					"error"
				);
			});
	}, [showNotification]);

    useEffect(() => {
		if (isLoading || !activeTab) {
			return;
		}
		const controller = new AbortController();
		setIsArticleLoading(true);
		setArticles([]);
		setPagination(null);

		api.get(`/article/listArticle.php?page=${page}&tab=${activeTab}&sort=${sortBy}`, { signal: controller.signal })
			.then(response => {
				setArticles(response.data.articles);
				setPagination(response.data.pagination);
			})
			.catch(error => {
				if (controller.signal.aborted) {
						return;
				}

				showNotification(
					error.response?.data?.message || "Failed to load articles",
					"error"
				);
			})
			.finally(() => {
				if (!controller.signal.aborted) setIsArticleLoading(false);
			});
			
		return () => controller.abort();
	}, [page, activeTab, sortBy, isLoading, showNotification]);

	useEffect(() => {
		if (isLoading) return;
		setActiveTab(isLoggedIn ? "for-you" : "news");
		setPage(1);
	}, [isLoggedIn, isLoading]);

	const handleArticleClick = (article) => {
		navigate(`/article/${article.article_id}`);
	};

	const handleTabChange = (tab) => {
		setActiveTab(tab);
		setPage(1);
	};

	const handleSortToggle = () => {
		setSortBy((previousSort) => {
			const index = sortOptions.findIndex((option) => option.value === previousSort);
			return sortOptions[(index + 1) % sortOptions.length].value;
		});

		setPage(1);
	};

    return (
        <section className="news-list">
			<div className="category-tabs">
				{isLoggedIn && (
					<button
						className={activeTab === "for-you" ? "active" : ""}
						onClick={() => handleTabChange("for-you")}
					>
						For You
					</button>
				)}
				<button
					className={activeTab === "news" ? "active" : ""}
					onClick={() => handleTabChange("news")}
				>
					News
				</button>
				{visibleCategories.map((category) => (
					<button
						key={category.category_id}
						className={activeTab === category.category_id ? "active" : ""}
						onClick={() => handleTabChange(category.category_id)}
					>
						{category.category_name}
					</button>
				))}
				{moreCategories.length > 0 && (
					<div className="more-category-menu">
						<button
							className={
								moreCategories.some((category) => category.category_id === activeTab) ? "active"
									: ""
							}
							onClick={() => setShowMoreCategories(!showMoreCategories)}
						>
							More
						</button>

						{showMoreCategories && (
							<div className="more-category-options">
								{moreCategories.map((category) => (
									<button
										key={category.category_id}
										onClick={() => {
											handleTabChange(category.category_id);
											setShowMoreCategories(false);
										}}
									>
										{category.category_name}
									</button>
								))}
							</div>
						)}
					</div>
				)}
			</div>
			{activeTab && activeTab !== "for-you" && (
				<div className="article-sort-row">
					<button
						className="article-sort-button"
						onClick={handleSortToggle}
					>
						<ListSortDescending size={18} />
						<span>
							Sort by: {sortOptions.find((option) => option.value === sortBy).label}
						</span>
					</button>
				</div>
			)}
			<div className="news-list-content">
				{isArticleLoading ? (
					<Loading />
				) : (
					<>
						{articles.map((article) => (
							<NewsCard
								key={article.article_id}
								article={article}
								onClick={() => handleArticleClick(article)}
							/>
						))}
						{pagination && pagination.total_pages > 1 && (
							<Pagination
								page={pagination.page}
								totalPages={pagination.total_pages}
								onPageChange={setPage}
							/>
						)}
					</>
				)}
			</div>
        </section>
    );
}