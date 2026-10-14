import { useEffect, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import api from "../../api/axios";
import Loading from "../Loading";
import Pagination from "../Pagination";
import ReportReview from "./ReportReview";
import "./ReportedContent.css";

export default function ReportedContent() {
	const [reports, setReports] = useState([]);
	const [page, setPage] = useState(1);
	const [pagination, setPagination] = useState(null);
	const [selectedReport, setSelectedReport] = useState(null);
	const [isReportsLoading, setIsReportsLoading] = useState(true);
	const { showNotification } = useNotification();
	const [refreshCount, setRefreshCount] = useState(0);

	useEffect(() => {
		setIsReportsLoading(true);
		setReports([]);
		setPagination(null);

		api.get(`/admin/listReportedContent.php?page=${page}`)
			.then(response => {
				if (page > response.data.pagination.total_pages && page > 1) {
					setPage(Math.max(1, response.data.pagination.total_pages));
					return;
				}
				setReports(response.data.reports);
				setPagination(response.data.pagination);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load reports",
					"error"
				);
			})
			.finally(() => {
				setIsReportsLoading(false);
			});
	}, [page, refreshCount, showNotification]);

	if (isReportsLoading) {
		return <Loading />;
	}

	if (selectedReport) {
		return (
			<ReportReview
				articleId={selectedReport.article_id}
				commentId={selectedReport.comment_id}
				onBack={() => setSelectedReport(null)}
				onResolved={() => {
					setSelectedReport(null);
					setRefreshCount(prev => prev + 1);
				}}
			/>
		);
	}

	return (
		<section className="reported-content">
			<h1>Reported Content</h1>

			<p className="reported-subtitle">
				{pagination?.total || 0} {pagination?.total == 1 ? "item" : "items"} awaiting review
			</p>

			<div className="reported-list">
				{reports.length === 0 ? (
					<p className="reported-empty">No pending reports</p>
				) : (
					reports.map(report => (
						<div
							className="reported-item"
							key={
								report.article_id !== null
									? `article-${report.article_id}`
									: `comment-${report.comment_id}`
							}
						>
							<span
								className={`reported-type ${
									report.article_id !== null
										? "reported-article"
										: "reported-comment"
								}`}
							>
								{report.article_id !== null ? "Article" : "Comment"}
							</span>

							<div className="reported-details">
								<p className="reported-title">{report.preview}</p>
							</div>

							<p className="reported-count">
								Reported by {report.report_count} {report.report_count == 1 ? "user" : "users"}
							</p>

							<button
								className="reported-review"
								onClick={() => setSelectedReport({
									article_id: report.article_id,
									comment_id: report.comment_id
								})}
							>
								Review
							</button>
						</div>
					))
				)}
			</div>

			{pagination && pagination.total_pages > 1 && (
				<Pagination
					page={pagination.page}
					totalPages={pagination.total_pages}
					onPageChange={setPage}
				/>
			)}
		</section>
	);
}