from django import forms
from ..milestone import Milestone

class MilestoneForm(forms.ModelForm):
    """
    Form for creating and updating Milestones.
    Exposes title, status, description and notes.
    The project ForeignKey and created_by are set in the view.
    """
    class Meta:
        model = Milestone
        fields = [
            'title',
            'status',
            'description',
            'notes',
        ]
        widgets = {
            'title': forms.TextInput(attrs={'class': 'form-control'}),
            'status': forms.Select(attrs={'class': 'form-control'}),
            'description': forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
            'notes': forms.Textarea(attrs={'class': 'form-control', 'rows': 2}),
        }
        labels = {
            'title': 'Milestone Title',
            'status': 'Status',
            'description': 'Description',
            'notes': 'Notes',
        }

    def clean_title(self):
        title = self.cleaned_data.get('title', '').strip()
        if not title:
            raise forms.ValidationError('Title is required.')
        return title

    def clean_status(self):
        status = self.cleaned_data.get('status')
        if not status:
            raise forms.ValidationError('Status is required.')
        return status
