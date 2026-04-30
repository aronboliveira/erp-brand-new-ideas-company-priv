import React, { useState, memo } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  TextField,
  Button,
  SelectChangeEvent,
} from "@mui/material";
import { AllowanceFormModalProps } from "../../definitions/components";
import { postAllowance } from "./fetch/POST";
const AllowanceCreate = memo((props: AllowanceFormModalProps) => {
  const {
      open,
      onClose,
      employeeId,
      allowanceOptions,
      allowanceTypes,
      onSubmitSuccess,
    } = props,
    [formData, setFormData] = useState({
      allowance_option: "",
      title: "",
      type: "",
      amount: "",
    }),
    handleChange = (
      e:
        | React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
        | React.ChangeEvent<{ name?: string; value: unknown }>
        | SelectChangeEvent
    ) =>
      setFormData({
        ...formData,
        [e.target.name as string]: e.target.value,
      }),
    handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      const id = employeeId.toString();
      postAllowance({
        id,
        submitDispatch: onSubmitSuccess,
        close: onClose,
        formData,
      });
    };
  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth='sm'>
      <form onSubmit={handleSubmit}>
        <DialogTitle>Create Allowance</DialogTitle>
        <DialogContent dividers>
          <FormControl fullWidth margin='normal' required>
            <InputLabel id='allowance_option-label'>
              Allowance Options
            </InputLabel>
            <Select
              labelId='allowance_option-label'
              id='allowance_option'
              name='allowance_option'
              value={formData.allowance_option}
              onChange={handleChange}
            >
              {allowanceOptions.map(option => (
                <MenuItem key={option.value} value={option.value}>
                  {option.label}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <TextField
            fullWidth
            required
            margin='normal'
            label='Title'
            name='title'
            value={formData.title}
            onChange={handleChange}
          />
          <FormControl fullWidth margin='normal' required>
            <InputLabel id='type-label'>Type</InputLabel>
            <Select
              labelId='type-label'
              id='type'
              name='type'
              value={formData.type}
              onChange={handleChange}
            >
              {allowanceTypes.map(type => (
                <MenuItem key={type.value} value={type.value}>
                  {type.label}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <TextField
            fullWidth
            required
            margin='normal'
            label='Amount'
            name='amount'
            type='number'
            inputProps={{ step: "0.01" }}
            value={formData.amount}
            onChange={handleChange}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} variant='outlined'>
            Cancel
          </Button>
          <Button type='submit' variant='contained' color='primary'>
            Create
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
});
export default AllowanceCreate;
