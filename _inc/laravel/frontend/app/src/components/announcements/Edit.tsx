import React, { useState, JSX } from "react";
import axios from "axios";
import { useRouter } from "next/router";
import {
  Box,
  Button,
  Grid,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  SelectChangeEvent,
} from "@mui/material";
import SmartToyIcon from "@mui/icons-material/SmartToy";
import { AnnouncementUpdateFormProps } from "@/definitions/components";
export default function AnnouncementEdit({
  announcement,
  branch,
  departments,
  plan,
}: AnnouncementUpdateFormProps): JSX.Element {
  const router = useRouter(),
    [formData, setFormData] = useState({
      title: announcement.title || "",
      branch_id: announcement.branch_id.toString() || "",
      department_id: announcement.department_id.toString() || "",
      start_date: announcement.start_date || "",
      end_date: announcement.end_date || "",
      description: announcement.description || "",
    }),
    handleChange = (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => setFormData({ ...formData, [e.target.name]: e.target.value }),
    handleSelectChange = (e: SelectChangeEvent<string>) =>
      setFormData({ ...formData, [e.target.name]: e.target.value }),
    handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      try {
        const res = await axios.put(
          `/announcement/${announcement.id}`,
          formData,
          { headers: { "Content-Type": "application/json" } }
        );
        if (!res?.data) {
          console.error(
            `No data received when updating announcement id ${announcement.id}`
          );
          return;
        }
        router.push("/announcements");
      } catch (error: any) {
        console.error(`Error in handleSubmit: ${error.message}`);
      }
    };
  return (
    <Box
      component='form'
      id='announcement-update-form'
      onSubmit={handleSubmit}
      sx={{ p: 2 }}
    >
      <Box className='modal-body'>
        {plan.chatgpt === 1 && (
          <Box sx={{ textAlign: "right", mb: 2 }}>
            <Button
              variant='contained'
              size='small'
              startIcon={<SmartToyIcon />}
              data-size='md'
              data-ajax-popup-over='true'
              data-url='/generate/announcement'
              data-bs-placement='top'
              data-title='Generate content with AI'
            >
              Generate with AI
            </Button>
          </Box>
        )}
        <Grid container spacing={2}>
          <Grid item xs={12}>
            <TextField
              fullWidth
              id='title'
              name='title'
              label='Announcement Title'
              placeholder='Enter Announcement Title'
              value={formData.title}
              onChange={handleChange}
            />
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControl fullWidth>
              <InputLabel id='branch-label'>Branch</InputLabel>
              <Select
                labelId='branch-label'
                id='branch_id'
                name='branch_id'
                value={formData.branch_id}
                label='Branch'
                onChange={handleSelectChange}
              >
                {Object.entries(branch).map(([key, value]) => (
                  <MenuItem key={key} value={key}>
                    {value}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControl fullWidth>
              <InputLabel id='department-label'>Department</InputLabel>
              <Select
                labelId='department-label'
                id='department_id'
                name='department_id'
                value={formData.department_id}
                label='Department'
                onChange={handleSelectChange}
              >
                {Object.entries(departments).map(([key, value]) => (
                  <MenuItem key={key} value={key}>
                    {value}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={6}>
            <TextField
              fullWidth
              id='start_date'
              name='start_date'
              label='Announcement start Date'
              type='date'
              value={formData.start_date}
              onChange={handleChange}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>
          <Grid item xs={12} sm={6}>
            <TextField
              fullWidth
              id='end_date'
              name='end_date'
              label='Announcement End Date'
              type='date'
              value={formData.end_date}
              onChange={handleChange}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>
          <Grid item xs={12}>
            <TextField
              fullWidth
              id='description'
              name='description'
              label='Announcement Description'
              placeholder='Enter Announcement Description'
              multiline
              rows={4}
              value={formData.description}
              onChange={handleChange}
            />
          </Grid>
        </Grid>
      </Box>
      <Box
        className='modal-footer'
        sx={{ mt: 2, display: "flex", justifyContent: "flex-end", gap: 1 }}
      >
        <Button variant='outlined' onClick={() => router.back()}>
          Cancel
        </Button>
        <Button variant='contained' type='submit'>
          Update
        </Button>
      </Box>
    </Box>
  );
}
