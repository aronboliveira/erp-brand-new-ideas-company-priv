// components/AllowanceOptionUpdateModal.tsx
import React, { useState, useEffect, memo, useCallback } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Button,
  Box,
} from "@mui/material";
import { AllowanceOptionUpdateModalProps } from "../../definitions/components";
import { putAllowanceOption } from "./fetch/PUT";
const AllowanceOptionEdit = memo((props: AllowanceOptionUpdateModalProps) => {
  const { open, onClose, allowanceOption, onSubmitSuccess } = props,
    [name, setName] = useState(allowanceOption.name),
    [error, setError] = useState("");
  useEffect(() => {
    setName(allowanceOption.name);
  }, [allowanceOption]);
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      setName(e.target.value);
      if (e.target.value.trim() !== "") setError("");
    },
    handleSubmit = useCallback(
      async (e: React.FormEvent) => {
        e.preventDefault();
        if (name.trim() === "") {
          setError("Name is required");
          return;
        }
        putAllowanceOption({
          onClose,
          submissionDispatch: onSubmitSuccess,
          id: name,
        });
      },
      [putAllowanceOption, onClose, onSubmitSuccess, setError, setName]
    );
  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth='sm'>
      <form onSubmit={handleSubmit}>
        <DialogTitle>Update Allowance Option</DialogTitle>
        <DialogContent dividers>
          <Box mb={2}>
            <TextField
              fullWidth
              label='Name'
              name='name'
              value={name}
              onChange={handleChange}
              placeholder='Enter Allowance option Name'
              error={!!error}
              helperText={error}
              variant='outlined'
            />
          </Box>
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
export default AllowanceOptionEdit;
