import { useEffect, useMemo, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import { ArrowLeft, TriangleAlert, Ban } from "lucide-react";
import api from "../../api/axios";
import Loading from "../Loading";
import IssueWarningModal from "./IssueWarningModal";
import SuspendUserModal from "./SuspendUserModal";
import "./ReportReview.css";

export default function ReportReview({ articleId, commentId, onBack, onResolved }) {
	const [report, setReport] = useState(null);
	const [isReviewLoading, setIsReviewLoading] = useState(true);
	const [notes, setNotes] = useState("");
	const [showWarningModal, setShowWarningModal] = useState(false);
	const [showSuspendModal, setShowSuspendModal] = useState(false);
	const { showNotification } = useNotification();
	const isArticle = articleId !== null;
	const [isDismissing, setIsDismissing] = useState(false);

	useEffect(() => {
		setIsReviewLoading(true);
		setReport(null);

		const url = articleId !== null
			? `/admin/getReportReview.php?article_id=${articleId}`
			: `/admin/getReportReview.php?comment_id=${commentId}`;

		api.get(url)
			.then(response => {
				setReport(response.data.report);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load report",
					"error"
				);
			})
			.finally(() => {
				setIsReviewLoading(false);
			});
	}, [articleId, commentId, showNotification]);

	const reasons = useMemo(() => {
		const reasons = [];

		for (const reporter of report?.reporters ?? []) {
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
	}, [report]);

	const handleDismiss = async () => {
		if (isDismissing) {
			return;
		}

		setIsDismissing(true);

		try {
			const response = await api.post("/admin/dismissReport.php", {
				article_id: articleId,
				comment_id: commentId,
				notes
			});

			showNotification(response.data.message, "success");
			onResolved();
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to dismiss report",
				"error"
			);
		} finally {
			setIsDismissing(false);
		}
	};

	const handleWarning = async (reason) => {
		try {
			const response = await api.post("/admin/issueWarning.php", {
				article_id: articleId,
				comment_id: commentId,
				reason,
				notes
			});

			showNotification(response.data.message, "success");
			onResolved();
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message || "Failed to issue warning",
				"error"
			);
			return false;
		}
	};

	const handleSuspend = async (duration, reason) => {
		try {
			const response = await api.post("/admin/suspendUser.php", {
				article_id: articleId,
				comment_id: commentId,
				duration,
				reason,
				notes
			});

			showNotification(response.data.message, "success");
			onResolved();
			return true;
		} catch (error) {
			showNotification(
				error.response?.data?.message ||
					(duration === "ban" ? "Failed to ban user" : "Failed to suspend user"),
				"error"
			);
			return false;
		}
	};

	if (isReviewLoading) {
		return <Loading />;
	}

	if (!report) {
		return (
			<section className="report-review">
				<button className="review-back" onClick={onBack}>
					<ArrowLeft size={18} />
					Back to Reported Content
				</button>
			</section>
		);
	}

	return (
		<section className="report-review">
			<button className="review-back" onClick={onBack}>
				<ArrowLeft size={18} />
				Back to Reported Content
			</button>

			<h1>Review Reported {isArticle ? "Article" : "Comment"}</h1>

			<p className="review-meta">
				Reported by {report.reporters.length} {report.reporters.length === 1 ? "user" : "users"}
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
						<h2>{report.preview}</h2>
						<p className="review-author">
							Article by {report.author}
						</p>
						<a
							className="review-view"
							href={`/article/${report.article_id}`}
							target="_blank"
						>
							View article
						</a>
					</>
				) : (
					<>
						<p className="review-body">{report.preview}</p>
						<p className="review-author">
							Comment by {report.author}
						</p>
						<a
							className="review-view"
							href={`/article/${report.parent_article_id}`}
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

			<label className="review-notes-label" htmlFor="review-notes">
				Admin notes (optional)
			</label>
			<textarea
				id="review-notes"
				className="review-notes"
				value={notes}
				onChange={e => setNotes(e.target.value)}
				placeholder="Add context for this decision..."
			/>

			<div className="review-actions">
				<button
					className="review-dismiss"
					onClick={handleDismiss}
					disabled={isDismissing}
				>
					Dismiss Report
				</button>

				<button
					className="review-warning"
					onClick={() => setShowWarningModal(true)}
					disabled={isDismissing}
				>
					<TriangleAlert size={18} />
					Issue Warning
				</button>

				<button
					className="review-suspend"
					onClick={() => setShowSuspendModal(true)}
					disabled={isDismissing}
				>
					<Ban size={18} />
					Suspend/Ban User
				</button>
			</div>

			{showWarningModal && (
				<IssueWarningModal
					username={report.author}
					initialReason={
						isArticle
							? "Your article violated our community guidelines."
							: "Your comment violated our community guidelines."
					}
					warningCount={report.warning_count}
					suspensionCount={report.suspension_count}
					onConfirm={handleWarning}
					onClose={() => setShowWarningModal(false)}
				/>
			)}

			{showSuspendModal && (
				<SuspendUserModal
					username={report.author}
					initialReason={
						isArticle
							? "Your article violated our community guidelines."
							: "Your comment violated our community guidelines."
					}
					warningCount={report.warning_count}
					suspensionCount={report.suspension_count}
					onConfirm={handleSuspend}
					onClose={() => setShowSuspendModal(false)}
				/>
			)}
		</section>
	);
}