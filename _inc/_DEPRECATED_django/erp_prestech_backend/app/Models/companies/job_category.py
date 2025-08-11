from .._helpers.describable import Describable

class JobCategory(Describable):

  class Meta:
    db_table = 'job_categories'
    verbose_name_plural = 'Job Categories'

  def __str__(self) -> str:
    return self.title
