import { useEffect, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import { getTimeAgo } from "../../utils/date";
import api from "../../api/axios";
import Loading from "../Loading";
import Pagination from "../Pagination";
import ResolutionReview from "./ResolutionReview";
import "./ResolvedReport.css";

const actionLabels = {
	dismissed: "Dismiss",
	warning: "Warning",
	suspension: "Suspend",
	ban: "Ban"
};

export default function ResolvedReports() {
	const [reports, setReports] = useState([]);
	const [page, setPage] = useState(1);
	const [pagination, setPagination] = useState(null);
	const [isReportsLoading, setIsReportsLoading] = useState(true);
	const { showNotification } = useNotification();
	const [selectedResolution, setSelectedResolution] = useState(null);

	useEffect(() => {
		setIsReportsLoading(true);
		setReports([]);
		setPagination(null);

		api.get(`/admin/listResolvedReports.php?page=${page}`)
			.then(response => {
				setReports(response.data.reports);
				setPagination(response.data.pagination);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load resolved reports",
					"error"
				);
			})
			.finally(() => {
				setIsReportsLoading(false);
			});
	}, [page, showNotification]);

	if (isReportsLoading) {
		return <Loading />;
	}

	if (selectedResolution !== null) {
		return (
			<ResolutionReview
				resolutionId={selectedResolution}
				onBack={() => setSelectedResolution(null)}
			/>
		);
	}

	return (
		<section className="resolved-reports">
			<h1>Resolved Reports</h1>

			<p className="resolved-subtitle">
				{pagination?.total || 0} {pagination?.total == 1 ? "item" : "items"} resolved
			</p>

			{reports.length === 0 ? (
				<p className="resolved-empty">No resolved reports</p>
			) : (
				<div className="resolved-list">
					<div className="resolved-columns">
						<span>Resolution ID</span>
						<span>Date</span>
						<span>Actor</span>
						<span>Action</span>
						<span>Target</span>
					</div>

					{reports.map(report => (
						<div
							className="resolved-row"
							key={report.resolution_id}
							onClick={() => setSelectedResolution(report.resolution_id)}
						>
							<span>#{report.resolution_id}</span>

							<span className="resolved-date">
								{getTimeAgo(report.resolved_at)}
							</span>

							<span className="resolved-actor">
								{report.actor}
							</span>

							<span className={`resolved-action ${report.action}`}>
								{actionLabels[report.action]}
							</span>

							<span className="resolved-target">
								{report.article_id !== null
									? `Article #${report.article_id}`
									: `Comment #${report.comment_id}`}
							</span>
						</div>
					))}
				</div>
			)}

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