import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useModal } from "../context/ModalContext";
import { useNotification } from "../context/NotificationContext";
import api from "../api/axios";
import NewsCard from "./NewsCard";
import Pagination from "./Pagination";
import Loading from "./Loading";
import "./MyProfile.css";

export default function UserProfile() {
	const navigate = useNavigate();
	const { id } = useParams();
	const { user, isLoggedIn, refreshFollowing } = useAuth();
	const { openLogin } = useModal();
	const { showNotification } = useNotification();
	const [profileUser, setProfileUser] = useState(null);
	const [publishedArticles, setPublishedArticles] = useState([]);
	const [following, setFollowing] = useState(false);

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
		if (user && user.user_id === Number(id)) {
			navigate("/profile");
		}
	}, [user, id, navigate]);

	useEffect(() => {
		api.get(`/user/getUser.php?id=${id}`)
			.then(response => {
				setProfileUser(response.data.user);
			})
			.catch(error => {
				if (error.response?.status === 400 || error.response?.status === 404) {
					navigate("/");
					return;
				}
				showNotification(
					error.response?.data?.message || "Failed to load profile",
					"error"
				);
			});

		api.get(`/article/listUserArticle.php?user_id=${id}`)
			.then(response => {
				setPublishedArticles(response.data.articles);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load articles",
					"error"
				);
			});

		if (isLoggedIn) {
			api.get(`/user/checkFollow.php?id=${id}`)
				.then(response => {
					setFollowing(response.data.following);
				})
				.catch(error => {
					showNotification(
						error.response?.data?.message || "Failed to check follow status",
						"error"
					);
				});
		} else {
			setFollowing(false);
		}
	}, [id, isLoggedIn]);

	const handleArticleClick = (article) => {
		navigate(`/article/${article.article_id}`);
	};

	const handleFollow = async () => {
		if (!isLoggedIn) {
			openLogin();
			return;
		}

		try {
			const response = await api.post("/user/setFollow.php", {
				user_id: id
			});

			setFollowing(response.data.following);
			setProfileUser(prev => ({
				...prev,
				followers_count: response.data.followers_count
			}));

			await refreshFollowing();
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to follow user",
				"error"
			);
		}
	};

	if (!profileUser) {
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
								onClick={handleFollow}
							>
								{following ? "Unfollow" : "Follow"}
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
				<button className="active">
					Published
				</button>
			</div>

            <div className="published-articles">
				{visiblePublished.map(article => (
					<NewsCard
						key={article.article_id}
						article={article}
						onClick={() => handleArticleClick(article)}
					/>
				))}
			</div>
			{publishedArticles.length > articlesPerPage && (
				<Pagination
					page={publishedPage}
					totalPages={publishedTotalPages}
					onPageChange={setPublishedPage}
				/>
			)}			
        </section>
    );
}