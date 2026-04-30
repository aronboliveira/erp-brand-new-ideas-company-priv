"use client";
import React, { JSX } from "react";
import {
  Avatar,
  AvatarGroup,
  Box,
  Button,
  IconButton,
  Tooltip,
  Typography,
} from "@mui/material";
import { DataGrid, GridColDef } from "@mui/x-data-grid";
import { Add, Delete, Edit } from "@mui/icons-material";
import Link from "next/link";
import { User } from "@/definitions/helpers";
import { AssetsPageProps } from "@/definitions/components";
import AdminPage from "../admin/page";
export default function AssetsPage({
  assets,
  userCanCreate,
  userCanEdit,
  userCanDelete,
  dateFormat,
  priceFormat,
  avatarBasePath,
}: AssetsPageProps): JSX.Element {
  const columns: GridColDef[] = [
    {
      field: "name",
      headerName: "Name",
      flex: 1,
      renderCell: ({ value }) => (
        <Typography variant='body2' id={`asset-name-${value}`}>
          {value}
        </Typography>
      ),
    },
    {
      field: "users",
      headerName: "Users",
      flex: 1,
      renderCell: ({ value }) => (
        <AvatarGroup max={4} id={`asset-users-${value[0]?.id || "unknown"}`}>
          {value.map((user: User) => (
            <Tooltip key={user.id} title={user.name}>
              <Avatar
                alt={user.name}
                src={
                  user.avatar
                    ? `${avatarBasePath}/${user.avatar}`
                    : "/storage/uploads/avatar/avatar.png"
                }
                sx={{ width: 32, height: 32 }}
              />
            </Tooltip>
          ))}
        </AvatarGroup>
      ),
    },
    {
      field: "purchase_date",
      headerName: "Purchase Date",
      flex: 1,
      renderCell: ({ value }) => (
        <Typography id={`asset-purchase-${value}`} className='font-style'>
          {dateFormat(value)}
        </Typography>
      ),
    },
    {
      field: "supported_date",
      headerName: "Supported Date",
      flex: 1,
      renderCell: ({ value }) => (
        <Typography id={`asset-supported-${value}`} className='font-style'>
          {dateFormat(value)}
        </Typography>
      ),
    },
    {
      field: "amount",
      headerName: "Amount",
      flex: 1,
      renderCell: ({ value }) => (
        <Typography id={`asset-amount-${value}`} className='font-style'>
          {priceFormat(value)}
        </Typography>
      ),
    },
    {
      field: "description",
      headerName: "Description",
      flex: 2,
      renderCell: ({ value }) => (
        <Typography id={`asset-description-${value}`} className='font-style'>
          {value || "-"}
        </Typography>
      ),
    },
    {
      field: "actions",
      headerName: "Action",
      sortable: false,
      flex: 1,
      renderCell: ({ row }) => (
        <Box display='flex' gap={1} id={`asset-actions-${row.id}`}>
          {userCanEdit && (
            <Tooltip title='Edit'>
              <IconButton
                size='small'
                id={`edit-btn-${row.id}`}
                data-url={`/account-assets/${row.id}/edit`}
                data-size='lg'
                data-ajax-popup='true'
                data-title='Edit Assets'
              >
                <Edit sx={{ color: "#fff" }} />
              </IconButton>
            </Tooltip>
          )}
          {userCanDelete && (
            <Tooltip title='Delete'>
              <IconButton
                size='small'
                id={`delete-btn-${row.id}`}
                onClick={() => {
                  const form = document.getElementById(`delete-form-${row.id}`);
                  confirm("Are You Sure?\nThis action cannot be undone.") &&
                    form instanceof HTMLFormElement &&
                    form.submit();
                }}
              >
                <Delete sx={{ color: "#fff" }} />
              </IconButton>
            </Tooltip>
          )}
          <form
            id={`delete-form-${row.id}`}
            method='POST'
            action={`/account-assets/${row.id}`}
            style={{ display: "none" }}
          >
            <input type='hidden' name='_method' value='DELETE' />
          </form>
        </Box>
      ),
    },
  ];
  return (
    <AdminPage>
      <Box id='assets-page' className='p-4'>
        <Box
          display='flex'
          justifyContent='space-between'
          mb={3}
          id='assets-header'
        >
          <Typography variant='h5' component='h1' id='page-title'>
            Assets
          </Typography>
          {userCanCreate && (
            <Button
              variant='contained'
              size='small'
              startIcon={<Add />}
              id='create-btn'
              data-url='/account-assets/create'
              data-size='lg'
              data-ajax-popup='true'
              data-title='Create New Assets'
            >
              Create
            </Button>
          )}
        </Box>
        <Box mb={3} id='assets-breadcrumb'>
          <Link
            href='/dashboard'
            id='breadcrumb-dashboard'
            style={{ textDecoration: "none", color: "inherit" }}
          >
            Dashboard
          </Link>
          <Typography id='breadcrumb-assets' color='text.primary'>
            Assets
          </Typography>
        </Box>
        <div id='assets-datagrid'>
          <DataGrid
            rows={assets}
            getRowId={row => row.id}
            columns={columns}
            disableRowSelectionOnClick
          />
        </div>
      </Box>
    </AdminPage>
  );
}
