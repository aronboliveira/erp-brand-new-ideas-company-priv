"use client";
import { memo, useState } from "react";
import {
  Box,
  Button,
  DialogActions,
  DialogContent,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  TextField,
} from "@mui/material";
import { AssetEditModalProps } from "@/definitions/components";
const AssetEditModal = ({
  employeeOptions,
  initialData,
  onClose,
  onSubmit,
  aiEnabled = false,
}: AssetEditModalProps) => {
  const [form, setForm] = useState(initialData),
    handleChange = (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => setForm({ ...form, [e.target.name]: e.target.value }),
    handleEmployeeChange = (e: SelectChangeEvent<string[]>) =>
      setForm({
        ...form,
        employee_id:
          typeof e.target.value === "string"
            ? e.target.value.split(",")
            : e.target.value,
      }),
    handleSubmit = () => onSubmit(form);
  return (
    <>
      <DialogContent id='asset-edit-modal-content'>
        {aiEnabled && (
          <Box className='text-end mb-4'>
            <Button
              variant='contained'
              size='small'
              className='btn btn-primary btn-icon btn-sm'
              aria-label='Generate content with AI'
              data-ajax-popup-over='true'
              data-size='md'
              data-url='/generate/account-asset'
              data-bs-placement='top'
              data-title='Generate content with AI'
              id='btn-ai-generate'
            >
              <i className='fas fa-robot' />
              <span className='ml-1'>Generate with AI</span>
            </Button>
          </Box>
        )}{" "}
        <Box className='grid grid-cols-12 gap-4'>
          <FormControl className='col-span-12'>
            <InputLabel id='employee-select-label'>Employee</InputLabel>
            <Select
              labelId='employee-select-label'
              id='employee-select'
              name='employee_id'
              multiple
              value={form.employee_id}
              onChange={handleEmployeeChange}
              className='form-control select2'
            >
              {Object.entries(employeeOptions).map(([key, label]) => (
                <MenuItem key={key} value={key}>
                  {label}
                </MenuItem>
              ))}
            </Select>
          </FormControl>{" "}
          <TextField
            className='col-span-6'
            required
            label='Name'
            name='name'
            value={form.name}
            onChange={handleChange}
          />{" "}
          <TextField
            className='col-span-6'
            required
            label='Amount'
            type='number'
            inputProps={{ step: "0.01" }}
            name='amount'
            value={form.amount}
            onChange={handleChange}
          />{" "}
          <TextField
            className='col-span-6'
            label='Purchase Date'
            name='purchase_date'
            type='date'
            InputLabelProps={{ shrink: true }}
            value={form.purchase_date}
            onChange={handleChange}
          />{" "}
          <TextField
            className='col-span-6'
            label='Supported Date'
            name='supported_date'
            type='date'
            InputLabelProps={{ shrink: true }}
            value={form.supported_date}
            onChange={handleChange}
          />{" "}
          <TextField
            className='col-span-12'
            label='Description'
            name='description'
            multiline
            rows={3}
            value={form.description}
            onChange={handleChange}
          />
        </Box>
      </DialogContent>{" "}
      <DialogActions className='modal-footer'>
        <Button variant='outlined' onClick={onClose} id='btn-cancel-modal'>
          Cancel
        </Button>
        <Button
          variant='contained'
          onClick={handleSubmit}
          id='btn-submit-modal'
        >
          Update
        </Button>
      </DialogActions>
    </>
  );
};
export default memo(AssetEditModal);
