import { useEffect, useMemo, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import { ArrowLeft } from "lucide-react";
import api from "../../api/axios";
import Loading from "../Loading";
import "./ReportReview.css";

export default function ResolutionReview({ resolutionId, onBack }) {
	const [resolution, setResolution] = useState(null);
	const [isReviewLoading, setIsReviewLoading] = useState(true);
	const { showNotification } = useNotification();
	const isArticle = resolution?.article_id !== null;

	useEffect(() => {
		setIsReviewLoading(true);
		setResolution(null);

		api.get(`/admin/getResolutionReview.php?resolution_id=${resolutionId}`)
			.then(response => {
				setResolution(response.data.resolution);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load resolution",
					"error"
				);
			})
			.finally(() => {
				setIsReviewLoading(false);
			});
	}, [resolutionId, showNotification]);

	const reasons = useMemo(() => {
		const reasons = [];

		for (const reporter of resolution?.reporters ?? []) {
			const existingReason = reasons.find(
				item => item.reason === reporter.reason
			);

			if (existingReason) {
				existingReason.count++;
			} else {
				reasons.push({
					reason: reporter.reason,
					count: 1
				});
			}
		}

		return reasons;
	}, [resolution]);

	if (isReviewLoading) {
		return <Loading />;
	}

	if (!resolution) {
		return (
			<section className="report-review">
				<button className="review-back" onClick={onBack}>
					<ArrowLeft size={18} />
					Back to Resolved Reports
				</button>
			</section>
		);
	}

	return (
		<section className="report-review">
			<button className="review-back" onClick={onBack}>
				<ArrowLeft size={18} />
				Back to Resolved Reports
			</button>

			<h1>Resolution #{resolution.resolution_id}</h1>

			<p className="review-meta">
				Reported by {resolution.reporters.length} {resolution.reporters.length === 1 ? "user" : "users"}
			</p>

			<div className="review-content">
				<span
					className={`review-type ${
						isArticle ? "review-article" : "review-comment"
					}`}
				>
					{isArticle ? "Article" : "Comment"}
				</span>

				{isArticle ? (
					<>
						<h2>{resolution.preview}</h2>
						<p className="review-author">
							Article by {resolution.author}
						</p>
						<a
							className="review-view"
							href={`/article/${resolution.article_id}`}
							target="_blank"
						>
							View article
						</a>
					</>
				) : (
					<>
						<p className="review-body">{resolution.preview}</p>
						<p className="review-author">
							Comment by {resolution.author}
						</p>
						<a
							className="review-view"
							href={`/article/${resolution.parent_article_id}`}
							target="_blank"
						>
							View article
						</a>
					</>
				)}
			</div>

			<h3>Reported reasons</h3>
			<div className="review-reasons">
				<ul>
					{reasons.map(item => (
						<li key={item.reason}>
							{item.reason} — reported by {item.count} {item.count === 1 ? "user" : "users"}
						</li>
					))}
				</ul>
			</div>

			<h3>Admin notes</h3>
			<p className="resolution-notes">
				{resolution.notes || "No notes"}
			</p>
		</section>
	);
}