import { useState } from "react";
import "./AdminActionModal.css";

export default function IssueWarningModal({ username, initialReason = "", warningCount, suspensionCount, onConfirm, onClose, fromReport = true }) {
	const [reason, setReason] = useState(initialReason);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleSubmit = async (e) => {
		e.preventDefault();

		if (!reason.trim() || isSubmitting) {
			return;
		}

		setIsSubmitting(true);

		try {
			const success = await onConfirm(reason);

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
				<h2>Issue warning to {username}</h2>

				<p className="action-description">
					{fromReport && "The reported content will be removed. "}
					The user will receive a warning, which will be recorded in their account history.
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
					<label htmlFor="warning-reason">
						Reason (visible to the user)
					</label>

					<textarea
						id="warning-reason"
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
							className="action-warning"
							disabled={isSubmitting || !reason.trim()}
						>
							Send Warning
						</button>
					</div>
				</form>
			</div>
		</div>
	);
}