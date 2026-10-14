import { useEffect, useState } from "react";
import { useNotification } from "../../context/NotificationContext";
import { getTimeAgo } from "../../utils/date";
import api from "../../api/axios";
import Loading from "../Loading";
import Pagination from "../Pagination";
import "./ModerationRecord.css";

const actionLabels = {
	warning: "Warning",
	suspension: "Suspend",
	ban: "Ban",
	lift_suspension: "Lift Suspension",
	unban: "Unban"
};

export default function ModerationRecord() {
	const [records, setRecords] = useState([]);
	const [page, setPage] = useState(1);
	const [action, setAction] = useState("all");
	const [pagination, setPagination] = useState(null);
	const [isRecordsLoading, setIsRecordsLoading] = useState(true);
	const { showNotification } = useNotification();

	useEffect(() => {
		setIsRecordsLoading(true);
		setRecords([]);
		setPagination(null);

		api.get(`/admin/listModerationRecord.php?page=${page}&action=${action}`)
			.then(response => {
				setRecords(response.data.records);
				setPagination(response.data.pagination);
			})
			.catch(error => {
				showNotification(
					error.response?.data?.message || "Failed to load moderation records",
					"error"
				);
			})
			.finally(() => {
				setIsRecordsLoading(false);
			});
	}, [page, action, showNotification]);

	if (isRecordsLoading) {
		return <Loading />;
	}

	return (
		<section className="moderation-record">
			<h1>Moderation Record</h1>

			<p className="moderation-subtitle">
				{pagination?.total || 0} {pagination?.total == 1 ? "record" : "records"}
			</p>

			<div className="moderation-toolbar">
				<h2>Action log</h2>

				<select
					className="moderation-filter"
					value={action}
					onChange={e => {
						setAction(e.target.value);
						setPage(1);
					}}
				>
					<option value="all">All Actions</option>
					<option value="warning">Warning</option>
					<option value="suspension">Suspend</option>
					<option value="ban">Ban</option>
					<option value="lift_suspension">Lift Suspension</option>
					<option value="unban">Unban</option>
				</select>
			</div>

			{records.length === 0 ? (
				<p className="moderation-empty">No moderation records</p>
			) : (
				<div className="moderation-list">
					<div className="moderation-columns">
						<span>Moderation ID</span>
						<span>Date</span>
						<span>Actor</span>
						<span>Action</span>
						<span>Target user</span>
						<span>Reason</span>
					</div>

					{records.map(record => (
						<div
							className="moderation-row"
							key={record.history_id}
						>
							<span>#{record.history_id}</span>

							<span className="moderation-date">
								{getTimeAgo(record.created_at)}
							</span>

							<span className="moderation-actor">
								{record.actor}
							</span>

							<span className={`moderation-action ${record.action}`}>
								{actionLabels[record.action]}
							</span>

							<span className="moderation-target">
								{record.target} (UserId:#{record.user_id})
							</span>

							<p className="moderation-reason">
								{record.reason}
							</p>
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