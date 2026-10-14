import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { getTimeAgo } from "../utils/date";
import api from "../api/axios";
import ArticleActions from "./ArticleActions";
import CommentList from "./CommentList";
import Loading from "./Loading";
import "./Article.css";

const articleStatus = {
	1: "This article is active",
	3: "This article was deleted by the author",
	4: "This article was removed by an admin"
};

export default function Article({ onBackgroundChange }) {
	const { id } = useParams();
	const navigate = useNavigate();
	const { user } = useAuth();
	const [article, setArticle] = useState(null);
	const [errorMessage, setErrorMessage] = useState(null);

	useEffect(() => {
		const controller = new AbortController();
		setArticle(null);
		setErrorMessage(null);
		onBackgroundChange(null);

		const url = user?.role_id == 2
			? `/admin/getArticle.php?id=${id}`
			: `/article/getArticle.php?id=${id}`;

		api.get(url, { signal: controller.signal })
			.then(response => {
				setArticle(response.data.article);
				onBackgroundChange(response.data.article.background_url);
			})
			.catch(error => {
				if (controller.signal.aborted) {
					return;
				}

					if (error.response?.status === 400) {
					navigate("/");
					return;
				}
				
				setErrorMessage(
					error.response?.data?.message || "Failed to load article"
				);
			});

			return () => controller.abort();
	}, [id, user?.user_id, navigate, onBackgroundChange]);

	if (errorMessage) {
		return <div>{errorMessage}</div>;
	}

	if (!article) {
		return <Loading />;
	}

	return (
		<div className="article">
			<article>
				{user?.role_id == 2 && (
					<p className="article-status">
						{articleStatus[article.status]}
					</p>
				)}
				<h1>{article.title}</h1>
				<div className="article-meta">
					<button
						className="article-author"
						onClick={() => navigate(`/profile/${article.author_id}`)}
					>
						<div className="article-author-avatar">
							<img
								className="article-author-picture"
								src={article.author_profile_picture || "/default-profile.svg"}
								alt=""
							/>

							{article.author_profile_frame_url && (
								<img
									className="article-author-frame"
									src={article.author_profile_frame_url}
									alt=""
								/>
							)}
						</div>
						<b>{article.author}</b>
					</button>
					<span>|</span>
					<span>
						{article.updated_at
							? `Updated ${getTimeAgo(article.updated_at)}`
							: getTimeAgo(article.published_at)}
					</span>
				</div>

				{article.thumbnail && (
					<img
						className="article-thumbnail"
						src={article.thumbnail}
						alt={article.title}
					/>
				)}

				<div
					className="article-body"
					dangerouslySetInnerHTML={{
						__html: article.content
					}}
				/>
				<ArticleActions articleId={article.article_id} />
			</article>
			<CommentList articleId={article.article_id} />
		</div>
	);
}