import { useState } from "react";
import "./AdminActionModal.css";

const durations = [
	{ value: "7", label: "7 days" },
	{ value: "14", label: "14 days" },
	{ value: "30", label: "30 days" },
	{ value: "ban", label: "Ban user" }
];

export default function SuspendUserModal({ username, initialReason = "", warningCount, suspensionCount, onConfirm, onClose, fromReport = true }) {
	const [duration, setDuration] = useState("7");
	const [reason, setReason] = useState(initialReason);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const isBan = duration === "ban";

	const handleSubmit = async (e) => {
		e.preventDefault();

		if (!reason.trim() || isSubmitting) {
			return;
		}

		setIsSubmitting(true);

		try {
			const success = await onConfirm(duration, reason);

			if (success) {
				onClose();
			}
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<div
			className="admin-action-overlay"
			onClick={() => {
				if (!isSubmitting) {
					onClose();
				}
			}}
		>
			<div
				className="admin-action-modal"
				onClick={e => e.stopPropagation()}
			>
				<h2>{isBan ? "Ban" : "Suspend"} user {username}</h2>

				<p className="action-description">
					{fromReport && "The reported content will be removed. "}
					{isBan
						? "The user will be permanently banned."
						: "The user will be suspended for the selected duration."}
				</p>

				{(warningCount > 0 || suspensionCount > 0) && (
					<div className="action-history">
						<h3>Moderation history</h3>
						<p>
							{suspensionCount} {suspensionCount == 1 ? "suspension" : "suspensions"}, {warningCount} {warningCount == 1 ? "warning" : "warnings"} on record
						</p>
					</div>
				)}

				<form onSubmit={handleSubmit}>
					<p className="action-label">Duration</p>

					<div className="suspend-options">
						{durations.map(option => (
							<button
								type="button"
								key={option.value}
								className={
									duration === option.value ? "selected" : ""
								}
								onClick={() => setDuration(option.value)}
								disabled={isSubmitting}
							>
								{option.label}
							</button>
						))}
					</div>

					<label htmlFor="suspend-reason">
						Reason (visible to the user)
					</label>

					<textarea
						id="suspend-reason"
						value={reason}
						onChange={e => setReason(e.target.value)}
						maxLength={2000}
						disabled={isSubmitting}
						required
					/>

					<div className="action-buttons">
						<button
							type="button"
							className="action-cancel"
							onClick={onClose}
							disabled={isSubmitting}
						>
							Cancel
						</button>

						<button
							type="submit"
							className="action-suspend"
							disabled={isSubmitting || !reason.trim()}
						>
							{isBan ? "Ban User" : "Suspend User"}
						</button>
					</div>
				</form>
			</div>
		</div>
	);
}