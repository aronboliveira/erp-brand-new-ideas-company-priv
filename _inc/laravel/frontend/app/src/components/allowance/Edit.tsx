import React, { useState, useEffect, memo, useCallback } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  TextField,
  Button,
  SelectChangeEvent,
} from "@mui/material";
import { AllowanceUpdateModalProps } from "../../definitions/components";
import { putAllowance } from "./fetch/PUT";
const AllowanceEdit = memo((props: AllowanceUpdateModalProps) => {
  const {
    open,
    onClose,
    allowance,
    allowanceOptions,
    allowanceTypes,
    onSubmitSuccess,
  } = props;
  const [formData, setFormData] = useState({
    allowance_option: allowance.allowance_option,
    title: allowance.title,
    type: allowance.type,
    amount: allowance.amount,
  });
  useEffect(() => {
    setFormData({
      allowance_option: allowance.allowance_option,
      title: allowance.title,
      type: allowance.type,
      amount: allowance.amount,
    });
  }, [allowance]);
  const handleChange = (
    e:
      | React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
      | SelectChangeEvent
      | React.ChangeEvent<{ name?: string; value: unknown }>
  ) => setFormData({ ...formData, [e.target.name as string]: e.target.value });
  const handleSubmit = useCallback(
    async (e: React.FormEvent) => {
      e.preventDefault();
      const id = allowance.id?.toString() ?? "";
      putAllowance({
        id,
        formData,
        submitDispatch: onSubmitSuccess,
        close: onClose,
      });
    },
    [onClose, onSubmitSuccess, putAllowance]
  );
  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth='sm'>
      <form onSubmit={handleSubmit}>
        <DialogTitle>Update Allowance</DialogTitle>
        <DialogContent dividers>
          <Grid container spacing={2}>
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth required>
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
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                required
                label='Title'
                name='title'
                value={formData.title}
                onChange={handleChange}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth required>
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
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                required
                label='Amount'
                name='amount'
                type='number'
                inputProps={{ step: "0.01" }}
                value={formData.amount}
                onChange={handleChange}
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} variant='outlined'>
            Cancel
          </Button>
          <Button type='submit' variant='contained' color='primary'>
            Update
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
});
export default AllowanceEdit;
