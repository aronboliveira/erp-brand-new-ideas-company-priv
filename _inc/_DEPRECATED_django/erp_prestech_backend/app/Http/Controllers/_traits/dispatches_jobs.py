from typing import Any
class DispatchesJobs:
  def dispatch(self, job: Any) -> Any:
    # Synchronous dispatch replace with asynchronous if needed.
    return job()
  def dispatch_sync(self, job: Any) -> Any:
    return job()
