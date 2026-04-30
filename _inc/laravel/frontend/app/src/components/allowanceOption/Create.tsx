// components/AllowanceOptionModal.tsx
import React, { memo, useState } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Button,
  Box,
} from "@mui/material";
import { AllowanceOptionModalProps } from "../../definitions/components";
import { postAllowanceOption } from "./fetch/POST";
const AllowanceOptionModal = memo((props: AllowanceOptionModalProps) => {
  const { open, onClose, onSubmitSuccess } = props,
    [name, setName] = useState(""),
    [error, setError] = useState(""),
    handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      setName(e.target.value);
      if (e.target.value.trim() !== "") setError("");
      postAllowanceOption({
        bodyData: { name },
        onClose,
        successDispatch: onSubmitSuccess,
      });
    },
    handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      if (name.trim() === "") {
        setError("Name is required");
        return;
      }
    };
  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth='sm'>
      <form onSubmit={handleSubmit}>
        <DialogTitle>Create Allowance Option</DialogTitle>
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
            Create
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
});
export default AllowanceOptionModal;
