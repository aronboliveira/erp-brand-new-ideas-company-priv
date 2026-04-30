import React, { useState, JSX, memo } from "react";
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  Grid,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Box,
  SelectChangeEvent,
} from "@mui/material";
import SmartToyIcon from "@mui/icons-material/SmartToy";
import { AssetsCreateProps } from "@/definitions/components";
const AssetsCreate = memo(
  ({
    open,
    onClose,
    employeeOptions,
    plan,
  }: AssetsCreateProps): JSX.Element => {
    const [employeeIds, setEmployeeIds] = useState<string[]>([]),
      [name, setName] = useState(""),
      [amount, setAmount] = useState(""),
      [purchaseDate, setPurchaseDate] = useState(""),
      [supportedDate, setSupportedDate] = useState(""),
      [description, setDescription] = useState(""),
      handleSubmit = (e: React.FormEvent<HTMLFormElement>): void => {
        e.preventDefault();
        // TODO: Implement submission logic via axios or fetch API;
      },
      handleEmployeeChange = (event: SelectChangeEvent): void => {
        const {
          target: { value },
        } = event;
        setEmployeeIds(
          typeof value === "string" ? value.split(",") : (value as string[])
        );
      };
    return (
      <Dialog
        open={open}
        onClose={onClose}
        fullWidth
        maxWidth='md'
        id='create-account-asset-modal'
      >
        <DialogTitle id='modal-title'>Create Account Asset</DialogTitle>
        <form onSubmit={handleSubmit} id='account-assets-form'>
          <DialogContent dividers id='modal-body'>
            {plan.chatgpt === 1 && (
              <Box sx={{ textAlign: "end", mb: 2 }} id='ai-module'>
                <Button
                  variant='contained'
                  size='small'
                  startIcon={<SmartToyIcon />}
                  data-size='md'
                  data-ajax-popup-over='true'
                  data-url='/generate?type=account%20asset'
                  data-bs-placement='top'
                  data-title='Generate content with AI'
                  id='generate-ai-btn'
                >
                  Generate with AI
                </Button>
              </Box>
            )}
            <Grid container spacing={2} id='form-fields'>
              <Grid item xs={12} id='employee-field'>
                <FormControl fullWidth size='small' id='employee-formcontrol'>
                  <InputLabel id='employee-label'>Employee</InputLabel>
                  <Select
                    labelId='employee-label'
                    id='employee-select'
                    multiple
                    value={employeeIds.join(", ")}
                    label='Employee'
                    onChange={handleEmployeeChange}
                  >
                    {employeeOptions.map(opt => (
                      <MenuItem
                        key={opt.value}
                        value={opt.value}
                        id={`employee-option-${opt.value}`}
                      >
                        {opt.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} md={6} id='name-field'>
                <TextField
                  fullWidth
                  required
                  label='Name'
                  value={name}
                  onChange={e => setName(e.target.value)}
                  id='name-input'
                />
              </Grid>
              <Grid item xs={12} md={6} id='amount-field'>
                <TextField
                  fullWidth
                  required
                  label='Amount'
                  type='number'
                  inputProps={{ step: "0.01" }}
                  value={amount}
                  onChange={e => setAmount(e.target.value)}
                  id='amount-input'
                />
              </Grid>
              <Grid item xs={12} md={6} id='purchase-date-field'>
                <TextField
                  fullWidth
                  label='Purchase Date'
                  type='date'
                  InputLabelProps={{ shrink: true }}
                  value={purchaseDate}
                  onChange={e => setPurchaseDate(e.target.value)}
                  id='purchase-date-input'
                />
              </Grid>
              <Grid item xs={12} md={6} id='supported-date-field'>
                <TextField
                  fullWidth
                  label='Supported Date'
                  type='date'
                  InputLabelProps={{ shrink: true }}
                  value={supportedDate}
                  onChange={e => setSupportedDate(e.target.value)}
                  id='supported-date-input'
                />
              </Grid>
              <Grid item xs={12} id='description-field'>
                <TextField
                  fullWidth
                  label='Description'
                  multiline
                  rows={3}
                  value={description}
                  onChange={e => setDescription(e.target.value)}
                  id='description-input'
                />
              </Grid>
            </Grid>
          </DialogContent>
          <DialogActions id='modal-footer'>
            <Button
              variant='outlined'
              type='button'
              onClick={onClose}
              id='cancel-btn'
            >
              Cancel
            </Button>
            <Button variant='contained' type='submit' id='submit-btn'>
              Create
            </Button>
          </DialogActions>
        </form>
      </Dialog>
    );
  }
);
export default AssetsCreate;
