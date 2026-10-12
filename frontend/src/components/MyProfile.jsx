import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useNotification } from "../context/NotificationContext";
import api from "../api/axios";
import NewsCard from "./NewsCard";
import Pagination from "./Pagination";
import Loading from "./Loading";
import "./MyProfile.css";

export default function MyProfile() {
	const navigate = useNavigate();
	const { user, isLoggedIn, isLoading } = useAuth();
	const { showNotification } = useNotification();
    const [activeTab, setActiveTab] = useState("published");
	const [profileUser, setProfileUser] = useState(null);
	const [publishedArticles, setPublishedArticles] = useState([]);
	const [drafts, setDrafts] = useState([]);

	const [publishedPage, setPublishedPage] = useState(1);
	const articlesPerPage = 5;
	const publishedTotalPages = Math.max(1, Math.ceil(publishedArticles.length / articlesPerPage));
	const visiblePublished = publishedArticles.slice(
		(publishedPage - 1) * articlesPerPage,
		publishedPage * articlesPerPage
	);

	// null until the author has enough rated articles (see getUser.php)
	const trustScore = profileUser?.trust_score ?? null;
	const trustScoreClass =
		trustScore === null ? "none"
		: trustScore >= 80 ? "high"
		: trustScore >= 50 ? "medium"
		: "low";

	useEffect(() => {
		if (!isLoading && !isLoggedIn) {
			navigate("/");
		}
	}, [isLoading, isLoggedIn, navigate]);

	useEffect(() => {
		if (!user) {
			return;
		}

		api.get(`/user/getUser.php?id=${user.user_id}`)
			.then(response => {
				setProfileUser(response.data.user);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load profile",
					"error"
				);
			});
	}, [user]);

	useEffect(() => {
		if (!isLoggedIn) {
			return;
		}

		api.get("/article/listMyArticle.php")
			.then(response => {
				setPublishedArticles(response.data.published);
				setDrafts(response.data.drafts);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load articles", 
					"error"
				);
			});
	}, [isLoggedIn]);

	useEffect(() => {
		if (publishedPage > publishedTotalPages) {
			setPublishedPage(publishedTotalPages);
		}
	}, [publishedTotalPages, publishedPage]);

	const handleArticleClick = (article) => {
		if (article.is_scheduled == 1) {
			handleEdit(article.article_id, "/edit-published");
			return;
		}
		navigate(`/article/${article.article_id}`);
	};

	const handleEdit = (articleId, url) => {
		navigate(url, {
			state: { articleId }
		});
	};

	const handleDelete = async (articleId) => {
		if (!window.confirm("Are you sure you want to delete this article?")) {
			return;
		}
		try {
			const response = await api.post("/article/deleteArticle.php", {
				article_id: articleId
			});

			setPublishedArticles((prev) =>
				prev.filter(article => article.article_id !== articleId)
			);
			setDrafts((prev) =>
				prev.filter(article => article.article_id !== articleId)
			);

			showNotification(
				response.data.message, 
				"success"
			);
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to delete article",
				"error"
			);
		}
	};

	if (isLoading || !user || !profileUser) {
		return <Loading />;
	}

    return (
        <section className="my-profile">
			<div className="profile-header">
				<div className="profile-main">
					<div className="profile-avatar">
						<img
							className="profile-picture"
							src={profileUser.profile_picture || "/default-profile.svg"}
							alt={profileUser.username}
						/>
						{profileUser.profile_frame_url && (
							<img
								key={profileUser.profile_frame_url}
								className="profile-frame"
								src={profileUser.profile_frame_url}
								alt=""
							/>
						)}
					</div>
					<div className="profile-info">
						<div className="profile-name-row">
							<div>
								<h1>{profileUser.username}</h1>
								<div
									className={`trust-score ${trustScoreClass}`}
									title="How helpful this author's articles are to readers who usually disagree with each other"
								>
									Trust Score: {trustScore ?? "Not enough ratings"}
								</div>
							</div>
							<button
								className="edit-profile-button"
								onClick={() => navigate("/edit-profile")}
							>
								Edit profile
							</button>
						</div>
						<p className="profile-bio">
							{profileUser.bio}
						</p>
					</div>
				</div>
				<div className="profile-stats">
					<div className="profile-stat">
						<strong>{publishedArticles.length}</strong>
						<span>Published</span>
					</div>
					<div className="profile-stat">
						<strong>{profileUser.followers_count}</strong>
						<span>Followers</span>
					</div>
					<div className="profile-stat">
						<strong>{profileUser.following_count}</strong>
						<span>Following</span>
					</div>
				</div>
			</div>

            <div className="profile-tabs">
                <button
                    className={activeTab === "published" ? "active" : ""}
                    onClick={() => setActiveTab("published")}
                >
                    Published
                </button>

                <button
                    className={activeTab === "drafts" ? "active" : ""}
                    onClick={() => setActiveTab("drafts")}
                >
                    Drafts
                </button>
            </div>

            {activeTab === "published" && (
				<>
					<div className="published-articles">
						{visiblePublished.map(article => (
							<NewsCard
								key={article.article_id}
								article={article}
								onClick={() => handleArticleClick(article)}
							>
								<button 
									className="edit-button" 
									onClick={() => handleEdit(article.article_id, "/edit-published")}
								>
									Edit
								</button>
								<button 
									className="delete-button"
									onClick={() => handleDelete(article.article_id)}
								>
									Delete
								</button>
							</NewsCard>
						))}
					</div>
					{publishedArticles.length > articlesPerPage && (
                        <Pagination
                            page={publishedPage}
                            totalPages={publishedTotalPages}
                            onPageChange={setPublishedPage}
                        />
                    )}
				</>
            )}

            {activeTab === "drafts" && (
                <div className="draft-articles">
                    {drafts.map(article => (
                        <NewsCard 
							key={article.article_id} 
							article={article}
						>
                            <button 
								className="edit-button" 
								onClick={() => handleEdit(article.article_id, "/edit-draft")}
							>
								Edit
							</button>
                            <button 
								className="delete-button"
								onClick={() => handleDelete(article.article_id)}
							>
								Delete
							</button>
                        </NewsCard>
                    ))}
                </div>
            )}
        </section>
    );
}